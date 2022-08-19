<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;
use Tests\Orisai\Localization\Doubles\SimpleTranslatorGetter;

/**
 * @runTestsInSeparateProcesses
 */
final class TranslatorHolderTest extends TestCase
{

	public function testNotSet(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Call Orisai\Localization\TranslatorHolder::setTranslatorGetter() to use '
			. 'Orisai\Localization\TranslatorHolder::getTranslator(), '
			. 'Orisai\Localization\t() and Orisai\Localization\tm().',
		);

		TranslatorHolder::getTranslator();
	}

	public function testSet(): void
	{
		$processor = new LocaleProcessor();
		$translator = new DefaultTranslator(
			new Locales(
				$processor,
				'en',
				[],
				[],
			),
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
			$processor,
		);

		TranslatorHolder::setTranslatorGetter(new SimpleTranslatorGetter($translator));
		self::assertSame($translator, TranslatorHolder::getTranslator());
	}

}
