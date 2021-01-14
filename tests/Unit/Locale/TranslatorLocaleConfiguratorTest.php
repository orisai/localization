<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleSet;
use Orisai\Localization\Locale\TranslatorLocaleConfigurator;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\FakeLocaleConfigurator;
use Tests\Orisai\Localization\Doubles\FakeTranslator;

final class TranslatorLocaleConfiguratorTest extends TestCase
{

	public function test(): void
	{
		$processor = new LocaleProcessor();
		$locales = new LocaleSet($processor, 'en', [], []);
		$translator = new FakeTranslator($locales, $processor);
		$configurator = new FakeLocaleConfigurator();
		$mainConfigurator = new TranslatorLocaleConfigurator($translator, $configurator);

		$mainConfigurator->configure('cs');

		self::assertSame('cs', $configurator->getLanguageTag());
		self::assertSame('cs', $translator->getCurrentLocale()->getTag());
	}

}
