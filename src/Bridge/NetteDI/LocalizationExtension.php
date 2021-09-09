<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Latte\Engine;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\AccessorDefinition;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Localization\ITranslator;
use Nette\PhpGenerator\PhpLiteral;
use Nette\Schema\Expect;
use Nette\Schema\Schema;
use OriNette\DI\Definitions\DefinitionsLoader;
use Orisai\Localization\Bridge\Latte\TranslationFilters;
use Orisai\Localization\Bridge\Latte\TranslationMacros;
use Orisai\Localization\Bridge\NetteCaching\CachedCatalogue;
use Orisai\Localization\Bridge\NetteLocalization\NetteTranslator;
use Orisai\Localization\Bridge\Tracy\TranslationPanel;
use Orisai\Localization\ConfigurableTranslator;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\MessageFormatter;
use Orisai\Localization\Formatting\MessageFormatterFactory;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleConfigurator;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\LocaleResolverManager;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Locale\MultiLocaleConfigurator;
use Orisai\Localization\Locale\MultiLocaleResolver;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\Catalogue;
use Orisai\Localization\Resource\FileLoader;
use Orisai\Localization\Resource\Loader;
use Orisai\Localization\Resource\LoaderManager;
use Orisai\Localization\Resource\MultiLoader;
use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorGetter;
use stdClass;
use Tracy\Bar;
use function assert;
use function serialize;

/**
 * @property-read stdClass $config
 */
final class LocalizationExtension extends CompilerExtension
{

	private ServiceDefinition $translatorDefinition;

	private ServiceDefinition $loggerDefinition;

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::structure([
				'newMessages' => Expect::bool()->required(),
				'panel' => Expect::bool(false),
			]),
			'locale' => Expect::structure([
				'default' => Expect::string()->required(),
				'allowed' => Expect::listOf('string'),
				'fallback' => Expect::arrayOf('string'),
			]),
			'loaders' => Expect::arrayOf(
				DefinitionsLoader::schema(),
			),
			'directories' => Expect::arrayOf(
				Expect::string(),
			),
			'resolvers' => Expect::arrayOf(
				DefinitionsLoader::schema(),
			),
			'configurators' => Expect::arrayOf(
				DefinitionsLoader::schema(),
			),
		]);
	}

	public function loadConfiguration(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;
		$loader = new DefinitionsLoader($this->compiler);

		// Locales
		$processor = new LocaleProcessor();
		$locales = new Locales(
			$processor,
			$config->locale->default,
			$config->locale->allowed,
			$config->locale->fallback,
		);
		$localesDefinition = $builder->addDefinition($this->prefix('locales'))
			->setFactory('\unserialize(\'?\', [?])', [
				new PhpLiteral(serialize($locales)),
				Locale::class,
			])
			->setType(Locales::class);

		// Configurators

		$configuratorDefinitions = [];

		foreach ($config->configurators as $configuratorKey => $configuratorConfig) {
			$configuratorDefinition = $loader->loadDefinitionFromConfig(
				$configuratorConfig,
				$this->prefix('configurator.' . $configuratorKey),
			);

			$configuratorDefinitions[] = $configuratorDefinition;
		}

		if ($configuratorDefinitions !== []) {
			$builder->addDefinition($this->prefix('configurators'))
				->setFactory(MultiLocaleConfigurator::class, [$configuratorDefinitions])
				->setType(LocaleConfigurator::class);
		}

		// Locale processor
		$processorDefinition = $builder->addDefinition($this->prefix('locale.processor'))
			->setFactory(LocaleProcessor::class)
			->setType(LocaleProcessor::class);

		// Resolvers
		$resolverDefinitionNames = [];

		foreach ($config->resolvers as $resolverKey => $resolverConfig) {
			$resolverDefinition = $loader->loadDefinitionFromConfig(
				$resolverConfig,
				$this->prefix('resolver.' . $resolverKey),
			);

			$resolverDefinitionNames[] = $resolverDefinition instanceof Reference
				? $resolverDefinition->getValue()
				: $resolverDefinition->getName();
		}

		$resolverManagerDefinition = $builder->addDefinition($this->prefix('resolvers.manager'))
			->setFactory(LazyLocaleResolverManager::class, [$resolverDefinitionNames])
			->setType(LocaleResolverManager::class)
			->setAutowired(false);

		$rootResolverDefinition = $builder->addDefinition($this->prefix('resolvers'))
			->setFactory(MultiLocaleResolver::class, [$resolverManagerDefinition])
			->setType(LocaleResolver::class)
			->setAutowired(false);

		// Loaders

		$loaderDefinitionNames = [];

		if ($config->directories !== []) {
			$directoriesLoaderDefinition = $builder->addDefinition($this->prefix('loader._directories'))
				->setFactory(FileLoader::class, [
					'directories' => $config->directories,
				])
				->setType(Loader::class)
				->setAutowired(false);

			$loaderDefinitionNames[] = $directoriesLoaderDefinition->getName();
		}

		foreach ($config->loaders as $loaderKey => $loaderConfig) {
			$loaderDefinition = $loader->loadDefinitionFromConfig(
				$loaderConfig,
				$this->prefix('loader.' . $loaderKey),
			);

			$loaderDefinitionNames[] = $loaderDefinition instanceof Reference
				? $loaderDefinition->getValue()
				: $loaderDefinition->getName();
		}

		$loaderManagerDefinition = $builder->addDefinition($this->prefix('loaders.manager'))
			->setFactory(LazyLoaderManager::class, [$loaderDefinitionNames])
			->setType(LoaderManager::class)
			->setAutowired(false);

		$lazyLoaderDefinition = $builder->addDefinition($this->prefix('loaders'))
			->setFactory(MultiLoader::class, [$loaderManagerDefinition])
			->setType(Loader::class)
			->setAutowired(false);

		// Catalogue

		$catalogueDefinition = $builder->addDefinition($this->prefix('catalogue'))
			->setFactory(CachedCatalogue::class, [
				'loader' => $lazyLoaderDefinition,
				'debugMode' => $config->debug->newMessages,
			])
			->setType(Catalogue::class)
			->setAutowired(false);

		// Message formatter

		$messageFormatterDefinition = $builder->addDefinition($this->prefix('formatter'))
			->setFactory('?::create()', [new PhpLiteral(MessageFormatterFactory::class)])
			->setType(MessageFormatter::class)
			->setAutowired(false);

		// Logger

		$this->loggerDefinition = $loggerDefinition = $builder->addDefinition($this->prefix('logger'))
			->setFactory(TranslationsLogger::class)
			->setType(TranslationsLogger::class)
			->setAutowired(false);

		// Translator

		$translatorPrefix = $this->prefix('translator');
		$this->translatorDefinition = $translatorDefinition = $builder->addDefinition($translatorPrefix)
			->setFactory(
				DefaultTranslator::class,
				[
					$localesDefinition,
					$rootResolverDefinition,
					$catalogueDefinition,
					$messageFormatterDefinition,
					$loggerDefinition,
					$processorDefinition,
				],
			)
			->setType(ConfigurableTranslator::class)
			->setAutowired([Translator::class, ConfigurableTranslator::class]);

		$builder->addDefinition($this->prefix('translator.nette'))
			->setFactory(NetteTranslator::class, [$translatorDefinition])
			->setType(ITranslator::class);

		// Translator accessor

		$translatorGetterDefinition = new AccessorDefinition();
		$translatorGetterDefinition->setImplement(TranslatorGetter::class)
			->setReference(new Reference($translatorPrefix));
		$builder->addDefinition($this->prefix('translator.getter'), $translatorGetterDefinition);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		// Latte

		$latteFactoryName = $builder->getByType(ILatteFactory::class);
		if ($latteFactoryName !== null) {
			$latteFactoryDefinition = $builder->getDefinition($latteFactoryName);
			assert($latteFactoryDefinition instanceof FactoryDefinition);

			$latteFiltersDefinition = $builder->addDefinition($this->prefix('latte.filters'))
				->setFactory(TranslationFilters::class)
				->setType(TranslationFilters::class)
				->setAutowired(false);

			$latteFactoryDefinition->getResultDefinition()
				->addSetup('?->onCompile[] = static function(? $engine) { ?::install($engine->getCompiler()); }', [
					'@self',
					new PhpLiteral(Engine::class),
					new PhpLiteral(TranslationMacros::class),
				])
				->addSetup(
					'?->addProvider(?, ?)',
					['@self', 'translator', $this->translatorDefinition],
				)
				->addSetup('?->addFilter(?, ?)', ['@self', 'translate', [$latteFiltersDefinition, 'translate']]);
		}

		// Debug

		if ($config->debug->panel) {
			$this->translatorDefinition->addSetup(
				[self::class, 'setupPanel'],
				[
					"$this->name.panel",
					$builder->getDefinitionByType(Bar::class),
					$this->translatorDefinition,
					$this->loggerDefinition,
				],
			);
		}
	}

	public static function setupPanel(
		string $name,
		Bar $bar,
		Translator $translator,
		TranslationsLogger $translationsLogger
	): void
	{
		$bar->addPanel(
			new TranslationPanel($translator, $translationsLogger),
			$name,
		);
	}

}
