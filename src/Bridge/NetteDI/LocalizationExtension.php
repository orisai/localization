<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Latte\Engine;
use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\ContainerBuilder;
use Nette\DI\Definitions\AccessorDefinition;
use Nette\DI\Definitions\Definition;
use Nette\DI\Definitions\FactoryDefinition;
use Nette\DI\Definitions\Reference;
use Nette\DI\Definitions\ServiceDefinition;
use Nette\Localization\Translator as NetteTranslatorInterface;
use Nette\PhpGenerator\Literal;
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
use Orisai\Localization\TranslatorHolder;
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

		$this->registerConfigurators($builder, $config, $loader);

		$localesDefinition = $this->registerLocales($builder, $config);
		$localeProcessorDefinition = $this->registerLocaleProcessor($builder);

		$translatorDefinition = $this->registerTranslator(
			$builder,
			$localesDefinition,
			$this->registerResolver($builder, $config, $loader),
			$this->registerCatalogue(
				$builder,
				$config,
				$this->registerLoader($builder, $config, $loader),
			),
			$this->registerMessageFormatter($builder),
			$this->registerTranslationLogger($builder),
			$localeProcessorDefinition,
		);

		$this->registerNetteTranslator($builder, $translatorDefinition);

		$translatorGetterDefinition = $this->registerTranslatorGetter($builder, $translatorDefinition);

		$this->setupShortcut($translatorGetterDefinition);
	}

	private function registerLocales(ContainerBuilder $builder, stdClass $config): ServiceDefinition
	{
		$processor = new LocaleProcessor();
		$locales = new Locales(
			$processor,
			$config->locale->default,
			$config->locale->allowed,
			$config->locale->fallback,
		);

		return $builder->addDefinition($this->prefix('locales'))
			->setFactory('\unserialize(\'?\', [?])', [
				new Literal(serialize($locales)),
				Locale::class,
			])
			->setType(Locales::class);
	}

	private function registerConfigurators(ContainerBuilder $builder, stdClass $config, DefinitionsLoader $loader): void
	{
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
	}

	private function registerLocaleProcessor(ContainerBuilder $builder): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('locale.processor'))
			->setFactory(LocaleProcessor::class)
			->setType(LocaleProcessor::class);
	}

	private function registerResolver(
		ContainerBuilder $builder,
		stdClass $config,
		DefinitionsLoader $loader
	): ServiceDefinition
	{
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

		return $builder->addDefinition($this->prefix('resolvers'))
			->setFactory(MultiLocaleResolver::class, [$resolverManagerDefinition])
			->setType(LocaleResolver::class)
			->setAutowired(false);
	}

	private function registerLoader(
		ContainerBuilder $builder,
		stdClass $config,
		DefinitionsLoader $loader
	): ServiceDefinition
	{
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

		return $builder->addDefinition($this->prefix('loaders'))
			->setFactory(MultiLoader::class, [$loaderManagerDefinition])
			->setType(Loader::class)
			->setAutowired(false);
	}

	private function registerCatalogue(
		ContainerBuilder $builder,
		stdClass $config,
		Definition $lazyLoaderDefinition
	): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('catalogue'))
			->setFactory(CachedCatalogue::class, [
				'loader' => $lazyLoaderDefinition,
				'debugMode' => $config->debug->newMessages,
			])
			->setType(Catalogue::class)
			->setAutowired(false);
	}

	private function registerMessageFormatter(ContainerBuilder $builder): ServiceDefinition
	{
		return $builder->addDefinition($this->prefix('formatter'))
			->setFactory('?::create()', [new Literal(MessageFormatterFactory::class)])
			->setType(MessageFormatter::class)
			->setAutowired(false);
	}

	private function registerTranslationLogger(ContainerBuilder $builder): ServiceDefinition
	{
		return $this->loggerDefinition = $builder->addDefinition($this->prefix('logger'))
			->setFactory(TranslationsLogger::class)
			->setType(TranslationsLogger::class)
			->setAutowired(false);
	}

	private function registerTranslator(
		ContainerBuilder $builder,
		Definition $localesDefinition,
		Definition $rootResolverDefinition,
		Definition $catalogueDefinition,
		Definition $messageFormatterDefinition,
		Definition $loggerDefinition,
		Definition $processorDefinition
	): ServiceDefinition
	{
		return $this->translatorDefinition = $builder->addDefinition($this->prefix('translator'))
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
	}

	private function registerNetteTranslator(ContainerBuilder $builder, ServiceDefinition $translatorDefinition): void
	{
		$builder->addDefinition($this->prefix('translator.nette'))
			->setFactory(NetteTranslator::class, [$translatorDefinition])
			->setType(NetteTranslatorInterface::class);
	}

	private function registerTranslatorGetter(
		ContainerBuilder $builder,
		ServiceDefinition $translatorDefinition
	): AccessorDefinition
	{
		$translatorDefinitionName = $translatorDefinition->getName();
		assert($translatorDefinitionName !== null);

		$translatorGetterDefinition = new AccessorDefinition();
		$translatorGetterDefinition->setImplement(TranslatorGetter::class)
			->setReference(new Reference($translatorDefinitionName));
		$builder->addDefinition($this->prefix('translator.getter'), $translatorGetterDefinition);

		return $translatorGetterDefinition;
	}

	private function setupShortcut(AccessorDefinition $translatorGetterDefinition): void
	{
		$this->getInitialization()->addBody('?::setTranslatorGetter($this->getService(?));', [
			new Literal(TranslatorHolder::class),
			$translatorGetterDefinition->getName(),
		]);
	}

	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();
		$config = $this->config;

		$this->addTranslatorToLatte($builder);
		$this->addPanelToTranslator($builder, $config);
	}

	private function addTranslatorToLatte(ContainerBuilder $builder): void
	{
		$latteFactoryName = $builder->getByType(LatteFactory::class);
		if ($latteFactoryName === null) {
			return;
		}

		$latteFactoryDefinition = $builder->getDefinition($latteFactoryName);
		assert($latteFactoryDefinition instanceof FactoryDefinition);

		$latteFiltersDefinition = $builder->addDefinition($this->prefix('latte.filters'))
			->setFactory(TranslationFilters::class)
			->setType(TranslationFilters::class)
			->setAutowired(false);

		$latteEngineDefinition = $latteFactoryDefinition->getResultDefinition();
		$latteEngineDefinition
			->addSetup(
				[self::class, 'setupLatteBridge'],
				[
					'@self',
					$this->translatorDefinition,
					$latteFiltersDefinition,
				],
			);
	}

	private function addPanelToTranslator(ContainerBuilder $builder, stdClass $config): void
	{
		if (!$config->debug->panel) {
			return;
		}

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

	public static function setupLatteBridge(Engine $engine, Translator $translator, TranslationFilters $filters): void
	{
		$engine->onCompile[] = static function () use ($engine): void {
			TranslationMacros::install($engine->getCompiler());
		};
		$engine->addProvider('translator', $translator);
		$engine->addFilter('translate', [$filters, 'translate']);
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
