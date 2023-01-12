<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Bridge\NetteDI;

use Latte\Engine;
use OriNette\DI\Boot\ManualConfigurator;
use Orisai\Localization\Bridge\Latte\TranslationExtension;
use Orisai\Localization\Bridge\Latte\TranslationFilters;
use Orisai\Localization\Bridge\NetteCaching\CachedCatalogue;
use Orisai\Localization\Bridge\NetteDI\LazyLoaderManager;
use Orisai\Localization\Bridge\NetteDI\LazyLocaleResolverManager;
use Orisai\Localization\Bridge\NetteLocalization\NetteTranslator;
use Orisai\Localization\Bridge\Tracy\TranslationPanel;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\MessageFormatter;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\MultiLocaleResolver;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\MultiLoader;
use Orisai\Localization\Translator;
use Orisai\TranslationContracts\TranslatorGetter;
use Orisai\TranslationContracts\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use Tracy\Bar;
use function assert;
use function dirname;
use function mkdir;
use function Orisai\TranslationContracts\t;
use function version_compare;
use const PHP_VERSION_ID;

/**
 * @runTestsInSeparateProcesses
 */
final class LocalizationExtensionTest extends TestCase
{

	private string $rootDir;

	protected function setUp(): void
	{
		parent::setUp();

		$this->rootDir = dirname(__DIR__, 4);
		if (PHP_VERSION_ID < 8_01_00) {
			@mkdir("$this->rootDir/var/build");
		}
	}

	public function testMinimal(): void
	{
		$configurator = new ManualConfigurator($this->rootDir);
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/config.minimal.neon');

		$container = $configurator->createContainer();

		$translator = $container->getByType(Translator::class);
		self::assertInstanceOf(DefaultTranslator::class, $translator);
		self::assertSame('test', $translator->translate('test'));

		$getter = $container->getByType(TranslatorGetter::class);
		self::assertInstanceOf(TranslatorGetter::class, $getter);
		self::assertSame($translator, $getter->get());

		$processor = $container->getService('localization.locale.processor');
		self::assertInstanceOf(LocaleProcessor::class, $processor);
		self::assertSame($processor, $container->getByType(LocaleProcessor::class));
		assert($processor instanceof LocaleProcessor);

		self::assertSame('en', $translator->getDefaultLocale()->getTag());
		self::assertSame(['en'], $processor->localesToTagVariants($translator->getAllowedLocales()));
		self::assertSame('en', $translator->getCurrentLocale()->getTag());

		self::assertInstanceOf(
			LazyLocaleResolverManager::class,
			$container->getService('localization.resolvers.manager'),
		);
		self::assertInstanceOf(MultiLocaleResolver::class, $container->getService('localization.resolvers'));
		self::assertInstanceOf(
			LazyLoaderManager::class,
			$container->getService('localization.loaders.manager'),
		);
		self::assertInstanceOf(MultiLoader::class, $container->getService('localization.loaders'));
		self::assertInstanceOf(CachedCatalogue::class, $container->getService('localization.catalogue'));
		self::assertInstanceOf(MessageFormatter::class, $container->getService('localization.formatter'));
		self::assertInstanceOf(TranslationsLogger::class, $container->getService('localization.logger'));
		self::assertSame($translator, $container->getService('localization.translator'));
		self::assertInstanceOf(NetteTranslator::class, $container->getService('localization.translator.nette'));
		self::assertSame($translator, TranslatorHolder::getTranslator());

		if (version_compare(Engine::VERSION, '3', '<')) {
			self::assertFalse($container->hasService('localization.latte.filters'));
		} else {
			self::assertFalse($container->hasService('localization.latte.extension'));
		}

		self::assertSame('test', t('test'));
	}

	public function testFull(): void
	{
		$configurator = new ManualConfigurator($this->rootDir);
		$configurator->setForceReloadContainer();
		$configurator->addConfig(__DIR__ . '/config.full.neon');

		$container = $configurator->createContainer();

		$translator = $container->getByType(Translator::class);
		self::assertInstanceOf(DefaultTranslator::class, $translator);

		$processor = new LocaleProcessor();

		self::assertSame('en', $translator->getDefaultLocale()->getTag());
		self::assertSame(
			['cs', 'fr', 'de', 'sk', 'en'],
			$processor->localesToTagVariants($translator->getAllowedLocales()),
		);
		self::assertSame('en', $translator->getCurrentLocale()->getTag());

		self::assertInstanceOf(
			LazyLocaleResolverManager::class,
			$container->getService('localization.resolvers.manager'),
		);
		self::assertInstanceOf(MultiLocaleResolver::class, $container->getService('localization.resolvers'));
		self::assertInstanceOf(CachedCatalogue::class, $container->getService('localization.catalogue'));
		self::assertInstanceOf(MessageFormatter::class, $container->getService('localization.formatter'));
		self::assertInstanceOf(TranslationsLogger::class, $container->getService('localization.logger'));
		self::assertInstanceOf(DefaultTranslator::class, $container->getService('localization.translator'));
		self::assertInstanceOf(NetteTranslator::class, $container->getService('localization.translator.nette'));

		if (version_compare(Engine::VERSION, '3', '<')) {
			self::assertInstanceOf(TranslationFilters::class, $container->getService('localization.latte.filters'));
		} else {
			self::assertInstanceOf(TranslationExtension::class, $container->getService('localization.latte.extension'));
		}

		$bar = $container->getByType(Bar::class);
		self::assertInstanceOf(TranslationPanel::class, $bar->getPanel('localization.panel'));

		//TODO - fallbacks, loaders, resolvers, configurators
		// 	   - logger
	}

}
