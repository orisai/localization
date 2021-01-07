<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Exception\LocaleNotWhitelisted;
use Orisai\Localization\Exception\MalformedLocale;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Logging\TranslationsLogger;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;
use function array_shift;

final class DefaultTranslatorTest extends TestCase
{

	public function testTranslate(): void
	{
		$logger = new TranslationsLogger();
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs', 'de', 'is'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([
				'en' => [
					'apples' => 'I have {apples} apples.',
					'no-param' => 'No parameters',
				],
				'cs' => [
					'apples' => 'Já mám {apples} jablka.',
				],
				'de' => [],
				'de-AT' => [
					'apples' => 'Ich habe {apples} Äpfel. (at)',
				],
				'de-CH' => [],
			]),
			new IntlMessageFormatter(),
			$logger,
		);

		//TODO - fallback není třeba whitelistovat? jak k tomu přistupovat v multi resolverech? v chybových zprávách?
		self::assertSame('en', $translator->getCurrentLocale());
		self::assertSame('en', $translator->getDefaultLocale());
		self::assertSame(['cs', 'de', 'is', 'en'], $translator->getLocaleWhitelist());

		// Default locale
		self::assertSame('I have 5 apples.', $translator->translate('apples', ['apples' => 5]));

		// Default locale - using en
		self::assertSame('I have 5 apples.', $translator->translate('apples', ['apples' => 5], 'en'));

		// Default locale - using en (computed)
		self::assertSame('I have 5 apples.', $translator->translate('apples', ['apples' => 5], 'en-US'));

		// Whitelisted locale - using cs
		self::assertSame('Já mám 3 jablka.', $translator->translate('apples', ['apples' => 3], 'cs'));

		// Mutation of whitelisted locale (cs) - using cs (computed)
		self::assertSame('Já mám 3 jablka.', $translator->translate('apples', ['apples' => 3], 'cs-CZ'));

		// Whitelisted locale - using en (default) - translation in requested locale is missing
		//TODO - ověřit v loggeru? mám překlad jen pro defaultní jazyk
		self::assertSame('I have 3 apples.', $translator->translate('apples', ['apples' => 3], 'is'));

		// Mutation of whitelisted locale (de) - using de-AT
		self::assertSame('Ich habe 3 Äpfel. (at)', $translator->translate('apples', ['apples' => 3], 'de-AT'));

		// todo - automatický fallback na jinou jazykovou mutaci? (de-AT)
		//		- momentálně spadne na EN
		//self::assertSame('Ich habe 3 Äpfel. (at)', $translator->translate('apples', ['apples' => 3], 'de'));
		//self::assertSame('Ich habe 3 Äpfel. (at)', $translator->translate('apples', ['apples' => 3], 'de-CH'));

		//TODO - fallbacky (manuálně nastavené) - sk -> cs

		// Translation with no parameters
		self::assertSame('No parameters', $translator->translate('no-param'));

		// Missing translation
		self::assertSame('missing-translation', $translator->translate('missing-translation'));
		self::assertSame(
			'missing-translation',
			$translator->translate('missing-translation', ['unused parameter'], 'cs'),
		);
		self::assertSame(
			'missing-translation',
			$translator->translate('missing-translation', ['unused parameter'], 'cs'),
		);
		self::assertSame('another-missing-translation', $translator->translate('another-missing-translation'));

		// Logger
		$missingResources = $logger->getMissingResources();
		self::assertCount(2, $missingResources);

		$resource1 = array_shift($missingResources);
		self::assertSame(
			['missing-translation', 3, ['en', 'cs']],
			[$resource1->getMessage(), $resource1->getCount(), $resource1->getLocales()],
		);

		$resource2 = array_shift($missingResources);
		self::assertSame(
			['another-missing-translation', 1, ['en']],
			[$resource2->getMessage(), $resource2->getCount(), $resource2->getLocales()],
		);
	}

	public function testTranslateNotWhitelistedLocale(): void
	{
		$this->expectExceptionMessage(InvalidState::class);
		$this->expectExceptionMessage('Locale "fr" is not whitelisted. Whitelisted are: "cs, en"');

		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([
				'en' => [
					'apples' => 'I have {apples} apples.',
				],
			]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		$translator->translate('apples', [], 'fr');
	}

	public function testResolverExplicit(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver('cs'),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame('cs', $translator->getCurrentLocale());
	}

	public function testResolverExplicitWithNormalization(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver('CS'),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame('cs', $translator->getCurrentLocale());
	}

	public function testResolverTriggeredByTranslation(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver('cs'),
			new ArrayCatalogue([
				'cs' => [
					'apples' => 'Já mám {apples} jablka.',
				],
			]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame('Já mám 3 jablka.', $translator->translate('apples', ['apples' => 3]));
		self::assertSame('cs', $translator->getCurrentLocale());
	}

	public function testResolverNotWhitelistedLocale(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver('fr'),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame('en', $translator->getCurrentLocale());
	}

	/**
	 * @param array<string> $localeWhitelist
	 * @param array<string> $fallbacks
	 * @dataProvider provideValidation
	 */
	public function testValidation(
		string $defaultLocale,
		array $localeWhitelist,
		array $fallbacks,
		string $message
	): void
	{
		$this->expectException(MalformedLocale::class);
		$this->expectExceptionMessage($message);

		DefaultTranslator::fromRawLocales(
			$defaultLocale,
			$localeWhitelist,
			$fallbacks,
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);
	}

	/**
	 * @return array<mixed>
	 */
	public function provideValidation(): array
	{
		return [
			['eN', [], [], 'Invalid "eN" locale, use "en" format instead.'],
			['en', ['EN_US'], [], 'Invalid "EN_US" locale, use "en-US" format instead.'],
			['en', [], ['CS' => 'sk'], 'Invalid "CS" locale, use "cs" format instead.'],
			['en', [], ['SK' => 'cs'], 'Invalid "SK" locale, use "sk" format instead.'],
		];
	}

	public function testSetCurrentLocale(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		$translator->setCurrentLocale('cs');
		self::assertSame('cs', $translator->getCurrentLocale());
	}

	public function testSetCurrentLocaleTwice(): void
	{
		$this->expectExceptionMessage(InvalidState::class);
		$this->expectExceptionMessage(
			'Current locale already set. Ensure you call Orisai\Localization\DefaultTranslator::setCurrentLocale() only once and before translator is first used.',
		);

		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs', 'de'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		$translator->setCurrentLocale('cs');
		$translator->setCurrentLocale('de');
	}

	public function testSetCurrentLocaleAfterComputation(): void
	{
		$this->expectExceptionMessage(InvalidState::class);
		$this->expectExceptionMessage(
			'Current locale already set. Ensure you call Orisai\Localization\DefaultTranslator::setCurrentLocale() only once and before translator is first used.',
		);

		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([
				'en' => [
					'apples' => 'I have {apples} apples.',
				],
			]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame('I have 5 apples.', $translator->translate('apples', ['apples' => 5]));
		$translator->setCurrentLocale('cs');
	}

	public function testSetCurrentLocaleNotValid(): void
	{
		$this->expectException(MalformedLocale::class);
		$this->expectExceptionMessage('Invalid "+ěš" locale.');

		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs', 'de'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		$translator->setCurrentLocale('+ěš');
	}

	public function testSetCurrentLocaleNotWhitelisted(): void
	{
		$this->expectException(LocaleNotWhitelisted::class);
		$this->expectExceptionMessage('Locale "fr" is not whitelisted. Whitelisted are: "cs, de, en"');

		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['cs', 'de'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		$translator->setCurrentLocale('fr');
	}

	public function testWhitelistDefaultAddedOnlyOnce(): void
	{
		$translator = DefaultTranslator::fromValidLocales(
			'en',
			['en'],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);

		self::assertSame(['en'], $translator->getLocaleWhitelist());
	}

}
