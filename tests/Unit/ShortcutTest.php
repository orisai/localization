<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\TranslatableMessage;
use Orisai\Localization\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;
use Tests\Orisai\Localization\Doubles\SimpleTranslatorGetter;
use function Orisai\Localization\t;
use function Orisai\Localization\tm;

/**
 * @runTestsInSeparateProcesses
 */
final class ShortcutTest extends TestCase
{

	public function testNotSet(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Call Orisai\Localization\TranslatorHolder::setTranslatorGetter() ' .
			'to use Orisai\Localization\TranslatorHolder::getTranslator()',
		);

		t('test');
	}

	public function testTranslate(): void
	{
		$processor = new LocaleProcessor();
		$translator = new DefaultTranslator(
			new Locales(
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
		);
		TranslatorHolder::setTranslatorGetter(new SimpleTranslatorGetter($translator));

		self::assertSame('No parameters', t('no-param'));
		self::assertSame('No parameters', tm(new TranslatableMessage('no-param')));

		self::assertSame('I have 3 apples.', t('apples', ['apples' => 3]));
		self::assertSame('I have 3 apples.', tm(new TranslatableMessage('apples', ['apples' => 3])));

		self::assertSame('Já mám 3 jablka.', t('apples', ['apples' => 3], 'cs'));
		self::assertSame('Já mám 3 jablka.', tm(new TranslatableMessage('apples', ['apples' => 3], 'cs')));
		self::assertSame('Já mám 3 jablka.', tm(new TranslatableMessage('apples', ['apples' => 3]), 'cs'));
		self::assertSame('Já mám 3 jablka.', tm(new TranslatableMessage('apples', ['apples' => 3], 'en'), 'cs'));
	}

}
