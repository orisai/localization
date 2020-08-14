<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Exception\MalformedLocale;
use Orisai\Localization\Locale\LocaleHelper;
use PHPUnit\Framework\TestCase;

final class LocaleHelperTest extends TestCase
{

	/**
	 * @doesNotPerformAssertions
	 * @dataProvider providerValidate
	 */
	public function testValidate(string $locale): void
	{
		LocaleHelper::validate($locale);
	}

	/**
	 * @return array<array<string>>
	 */
	public function providerValidate(): array
	{
		return [
			['cs'],
			['cs-CZ'],
			['en'],
			['en-US'],
		];
	}

	/**
	 * @dataProvider providerValidateFailure
	 */
	public function testValidateUnknownFormat(string $locale, string $message): void
	{
		$this->expectException(MalformedLocale::class);
		$this->expectExceptionMessage($message);

		LocaleHelper::validate($locale);
	}

	/**
	 * @return array<array<string>>
	 */
	public function providerValidateFailure(): array
	{
		return [
			['+ěšč', 'Invalid "+ěšč" locale.'],
			['En', 'Invalid "En" locale, use "en" format instead.'],
			['EN', 'Invalid "EN" locale, use "en" format instead.'],
			['EN_us', 'Invalid "EN_us" locale, use "en-US" format instead.'],
			['en_us', 'Invalid "en_us" locale, use "en-US" format instead.'],
			['_en_us', 'Invalid "_en_us" locale, use "en-US" format instead.'],
			['en-us-', 'Invalid "en-us-" locale, use "en-US" format instead.'],
		];
	}

	/**
	 * @dataProvider providerNormalize
	 */
	public function testNormalize(string $given, string $expected): void
	{
		self::assertSame($expected, LocaleHelper::normalize($given));
	}

	/**
	 * @return array<array<string>>
	 */
	public function providerNormalize(): array
	{
		return [
			['CS', 'cs'],
			['Cs', 'cs'],
			['cs_cz', 'cs-CZ'],
			['cs-Cz', 'cs-CZ'],
		];
	}

	/**
	 * @dataProvider providerShorten
	 */
	public function testShorten(string $given, string $expected): void
	{
		self::assertSame($expected, LocaleHelper::shorten($given));
	}

	/**
	 * @return array<array<string>>
	 */
	public function providerShorten(): array
	{
		return [
			['en-US', 'en'],
			['en', 'en'],
			['afa', 'afa'],
			['afa-EG', 'afa'],
		];
	}

	/**
	 * @param array<string> $whitelist
	 * @dataProvider providerIsWhitelisted
	 */
	public function testIsWhitelisted(string $locale, array $whitelist): void
	{
		self::assertTrue(LocaleHelper::isWhitelisted($locale, $whitelist));
	}

	/**
	 * @return array<mixed>
	 */
	public function providerIsWhitelisted(): array
	{
		return [
			[
				'en',
				['en'],
			],
			[
				'en-US',
				['en'],
			],
			[
				'en-GB',
				['en-GB'],
			],
		];
	}

	/**
	 * @param array<string> $whitelist
	 * @dataProvider providerIsNotWhitelisted
	 */
	public function testIsNotWhitelisted(string $locale, array $whitelist): void
	{
		self::assertFalse(LocaleHelper::isWhitelisted($locale, $whitelist));
	}

	/**
	 * @return array<mixed>
	 */
	public function providerIsNotWhitelisted(): array
	{
		return [
			[
				'en',
				[],
			],
			[
				'en',
				['cs', 'de', 'sk'],
			],
			[
				'en',
				['en-GB'],
			],
			[
				'en-US',
				['en-GB'],
			],
			[
				'en-GB',
				[],
			],
		];
	}

}
