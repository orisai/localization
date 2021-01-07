<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Bridge\NetteDI;

use Nette\DI\Compiler;
use Nette\DI\Container;
use Nette\DI\ContainerLoader;
use Nette\DI\Extensions\ExtensionsExtension;
use Orisai\Localization\Bridge\Latte\TranslationFilters;
use Orisai\Localization\Bridge\NetteCaching\CachedCatalogue;
use Orisai\Localization\Bridge\NetteDI\LazyMultiLoader;
use Orisai\Localization\Bridge\NetteDI\LazyMultiLocaleResolver;
use Orisai\Localization\Bridge\NetteDI\LazyTranslator;
use Orisai\Localization\Bridge\NetteLocalization\NetteTranslator;
use Orisai\Localization\Bridge\Tracy\TranslationPanel;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\MessageFormatter;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\ArrayCacheCatalogue;
use Orisai\Localization\Resource\ArrayCacheLoader;
use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use function assert;
use function Orisai\Localization\__;

/**
 * @runTestsInSeparateProcesses
 */
final class LocalizationExtensionTest extends TestCase
{

	private const TEMP_PATH = __DIR__ . '/../../../../var/tmp';

	public function testMinimal(): void
	{
		$loader = new ContainerLoader(self::TEMP_PATH . '/cache', true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('extensions', new ExtensionsExtension());
			$compiler->loadConfig(__DIR__ . '/config.minimal.neon');
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);
		$container->initialize();

		$translator = $container->getByType(Translator::class);
		self::assertInstanceOf(DefaultTranslator::class, $translator);

		self::assertSame('en', $translator->getDefaultLocale());
		self::assertSame(['en'], $translator->getLocaleWhitelist());
		self::assertSame('en', $translator->getCurrentLocale());

		self::assertInstanceOf(LazyMultiLocaleResolver::class, $container->getService('localization.resolver'));
		self::assertInstanceOf(LazyMultiLoader::class, $container->getService('localization.loader'));
		self::assertInstanceOf(ArrayCacheLoader::class, $container->getService('localization.loader.cache'));
		self::assertInstanceOf(CachedCatalogue::class, $container->getService('localization.catalogue'));
		self::assertInstanceOf(
			ArrayCacheCatalogue::class,
			$container->getService('localization.catalogue.cache'),
		);
		self::assertInstanceOf(MessageFormatter::class, $container->getService('localization.formatter'));
		self::assertInstanceOf(TranslationsLogger::class, $container->getService('localization.logger'));
		self::assertInstanceOf(DefaultTranslator::class, $container->getService('localization.translator'));
		self::assertInstanceOf(NetteTranslator::class, $container->getService('localization.translator.nette'));
		self::assertInstanceOf(LazyTranslator::class, $container->getService('localization.translator.lazy'));
		self::assertFalse($container->hasService('localization.tracy.panel'));
		self::assertFalse($container->hasService('localization.latte.filters'));
		self::assertInstanceOf(LazyTranslator::class, TranslatorHolder::getInstance()->getTranslator());
		self::assertSame('test', __('test'));
	}

	public function testFull(): void
	{
		$loader = new ContainerLoader(self::TEMP_PATH . '/cache', true);
		$class = $loader->load(static function (Compiler $compiler): void {
			$compiler->addExtension('extensions', new ExtensionsExtension());
			$compiler->addConfig([
				'parameters' => [
					'tempDir' => self::TEMP_PATH,
				],
			]);
			$compiler->loadConfig(__DIR__ . '/config.full.neon');
		}, __METHOD__);

		$container = new $class();
		assert($container instanceof Container);
		$container->initialize();

		$translator = $container->getByType(Translator::class);
		self::assertInstanceOf(DefaultTranslator::class, $translator);

		self::assertSame('en', $translator->getDefaultLocale());
		self::assertSame(['cs', 'fr', 'de', 'sk', 'en'], $translator->getLocaleWhitelist());
		self::assertSame('en', $translator->getCurrentLocale());

		self::assertInstanceOf(LazyMultiLocaleResolver::class, $container->getService('localization.resolver'));
		self::assertInstanceOf(LazyMultiLoader::class, $container->getService('localization.loader'));
		self::assertInstanceOf(ArrayCacheLoader::class, $container->getService('localization.loader.cache'));
		self::assertInstanceOf(CachedCatalogue::class, $container->getService('localization.catalogue'));
		self::assertInstanceOf(
			ArrayCacheCatalogue::class,
			$container->getService('localization.catalogue.cache'),
		);
		self::assertInstanceOf(MessageFormatter::class, $container->getService('localization.formatter'));
		self::assertInstanceOf(TranslationsLogger::class, $container->getService('localization.logger'));
		self::assertInstanceOf(DefaultTranslator::class, $container->getService('localization.translator'));
		self::assertInstanceOf(NetteTranslator::class, $container->getService('localization.translator.nette'));
		self::assertFalse($container->hasService('localization.translator.lazy'));
		self::assertInstanceOf(TranslationPanel::class, $container->getService('localization.tracy.panel'));
		self::assertInstanceOf(TranslationFilters::class, $container->getService('localization.latte.filters'));

		//TODO - fallbacks, loaders, resolvers, configurators
		// 	   - logger
	}

}
