<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleSet;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;
use function function_exists;
use function Orisai\Localization\__;

/**
 * @runTestsInSeparateProcesses
 */
final class ShortcutTest extends TestCase
{

	public function test(): void
	{
		$processor = new LocaleProcessor();
		TranslatorHolder::setTranslator(
			new DefaultTranslator(
				new LocaleSet(
					$processor,
					'en',
					['cs'],
					[],
				),
				new FakeLocaleResolver(),
				new ArrayCatalogue([
					'en' => [
						'no-param' => 'No parameters',
						'apples' => 'I have {apples} apples.',
					],
					'cs' => [
						'apples' => 'Já mám {apples} jablka.',
					],
				]),
				new IntlMessageFormatter(),
				new TranslationsLogger(),
				$processor,
			),
		);

		self::assertTrue(function_exists('Orisai\Localization\__'));

		self::assertFalse(isset($GLOBALS[Translator::class]));
		self::assertSame('I have 3 apples.', __('apples', ['apples' => 3]));
		self::assertTrue(isset($GLOBALS[Translator::class]));

		self::assertSame('No parameters', __('no-param'));
		self::assertSame('I have 3 apples.', __('apples', ['apples' => 3]));
		self::assertSame('Já mám 3 jablka.', __('apples', ['apples' => 3], 'cs'));
	}

}
