<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Latte\Engine;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\Statement;
use Nette\Localization\ITranslator;
use Nette\PhpGenerator\ClassType;
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
use Orisai\Localization\Locale\LocaleSet;
use Orisai\Localization\Locale\MultiLocaleConfigurator;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\ArrayCacheCatalogue;
use Orisai\Localization\Resource\ArrayCacheLoader;
use Orisai\Localization\Resource\Catalogue;
use Orisai\Localization\Resource\Loader;
use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorHolder;
use stdClass;
use function assert;
use function serialize;

/**
 * @property-read stdClass $config
 */
final class LocalizationExtension extends CompilerExtension
{

	public function getConfigSchema(): Schema
	{
		return Expect::structure([
			'debug' => Expect::structure([
				'panel' => Expect::bool(false),
			]),
			'holder' => Expect::structure([
				'enabled' => Expect::bool(true),
			]),
			'locale' => Expect::structure([
				'default' => Expect::string()->required(),
				'allowed' => Expect::listOf('string'),
				'fallback' => Expect::arrayOf('string'),
			]),
			'loaders' => Expect::arrayOf(
				Expect::anyOf(
					Expect::string(),
					Expect::array(),
					Expect::type(Statement::class),
				),
			),
			'resolvers' => Expect::arrayOf(
				Expect::anyOf(
					Expect::string(),
					Expect::array(),
					Expect::type(Statement::class),
				),
			),
			'configurators' => Expect::arrayOf(
				Expect::anyOf(
					Expect::string(),
					Expect::array(),
					Expect::type(Statement::class),
				),
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
		$locales = new LocaleSet(
			$processor,
			$config->locale->default,
			$config->locale->allowed,
			$config->locale->fallback,
		);
		$localesDef = $builder->addDefinition($this->prefix('locales'))
			->setFactory('\unserialize(\'?\', [?])', [
				new PhpLiteral(serialize($locales)),
				Locale::class,
			])
			->setType(LocaleSet::class);

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
			$builder->addDefinition($this->prefix('configurator'))
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

		$rootResolverDefinition = $builder->addDefinition($this->prefix('resolver'))
			->setFactory(LazyMultiLocaleResolver::class, [$resolverDefinitionNames])
			->setType(LocaleResolver::class)
			->setAutowired(false);

		// Loaders

		$loaderDefinitionNames = [];

		foreach ($config->loaders as $loaderKey => $loaderConfig) {
			$loaderDefinition = $loader->loadDefinitionFromConfig(
				$loaderConfig,
				$this->prefix('loader.' . $loaderKey),
			);

			$loaderDefinitionNames[] = $loaderDefinition instanceof Reference
				? $loaderDefinition->getValue()
				: $loaderDefinition->getName();
		}

		$lazyLoaderDefinition = $builder->addDefinition($this->prefix('loader'))
			->setFactory(LazyMultiLoader::class, [$loaderDefinitionNames])
			->setType(Loader::class)
			->setAutowired(false);

		$loaderCacheDefinition = $builder->addDefinition($this->prefix('loader.cache'))
			->setFactory(ArrayCacheLoader::class, [$lazyLoaderDefinition])
			->setType(Loader::class)
			->setAutowired(false);

		// Catalogue

		$catalogueDefinition = $builder->addDefinition($this->prefix('catalogue'))
			->setFactory(CachedCatalogue::class, [$loaderCacheDefinition])
			->setType(Catalogue::class)
			->setAutowired(false);

		$catalogueCacheDefinition = $builder->addDefinition($this->prefix('catalogue.cache'))
			->setFactory(ArrayCacheCatalogue::class, [$catalogueDefinition])
			->setType(Catalogue::class)
			->setAutowired(false);

		// Message formatter

		$messageFormatterDefinition = $builder->addDefinition($this->prefix('formatter'))
			->setFactory('?::create()', [new PhpLiteral(MessageFormatterFactory::class)])
			->setType(MessageFormatter::class)
			->setAutowired(false);

		// Logger

		$loggerDefinition = $builder->addDefinition($this->prefix('logger'))
			->setFactory(TranslationsLogger::class)
			->setType(TranslationsLogger::class)
			->setAutowired(false);

		// Translator

		$translatorPrefix = $this->prefix('translator');
		$translatorDefinition = $builder->addDefinition($translatorPrefix)
			->setFactory(
				DefaultTranslator::class,
				[
					$localesDef,
					$rootResolverDefinition,
					$catalogueCacheDefinition,
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

		// Shortcut

		if ($config->holder->enabled) {
			$builder->addDefinition($this->prefix('translator.lazy'))
				->setFactory(LazyTranslator::class, ['@container', $translatorPrefix])
				->setType(Translator::class)
				->setAutowired(false);
		}

		// Debug

		if ($config->debug->panel) {
			$builder->addDefinition($this->prefix('tracy.panel'))
				->setFactory(TranslationPanel::class, [$translatorDefinition, $loggerDefinition])
				->setType(TranslationPanel::class)
				->setAutowired(false);
		}
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

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
					['@self', 'translator', $builder->getDefinition($this->prefix('translator'))],
				)
				->addSetup('?->addFilter(?, ?)', ['@self', 'translate', [$latteFiltersDefinition, 'translate']]);
		}
	}

	public function afterCompile(ClassType $class): void
	{
		$config = $this->config;
		$initialize = $class->getMethod('initialize');

		// Debug

		if ($config->debug->panel) {
			$initialize->addBody('$this->getService(?)->addPanel($this->getService(?));', [
				'tracy.bar',
				$this->prefix('tracy.panel'),
			]);
		}

		// Shortcut

		if ($config->holder->enabled) {
			$initialize->addBody('?::setTranslator($this->getService(?));', [
				new PhpLiteral(TranslatorHolder::class),
				$this->prefix('translator.lazy'),
			]);
		}
	}

}
