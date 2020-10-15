<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Locale\TranslatorLocaleConfigurator;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\FakeLocaleConfigurator;
use Tests\Orisai\Localization\Doubles\FakeTranslator;

final class TranslatorLocaleConfiguratorTest extends TestCase
{

	public function test(): void
	{
		$translator = new FakeTranslator('en');
		$configurator = new FakeLocaleConfigurator();
		$mainConfigurator = new TranslatorLocaleConfigurator($translator, $configurator);

		$mainConfigurator->configure('cs');

		self::assertSame('cs', $configurator->getLocale());
		self::assertSame('cs', $translator->getCurrentLocale());
	}

}
