<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Generator;
use Orisai\Localization\Exception\MalformedLanguageTag;
use Orisai\Localization\Locale\GrandfatheredLocale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\PrivateLocale;
use Orisai\Localization\Locale\StandardLocale;
use PHPUnit\Framework\TestCase;
use function strtolower;

final class LocaleProcessorTest extends TestCase
{

	private LocaleProcessor $processor;

	protected function setUp(): void
	{
		parent::setUp();
		$this->processor = new LocaleProcessor();
	}

	/**
	 * @dataProvider provideStandardFormatAdding
	 * @dataProvider provideStandardFormatSingleOptional
	 * @dataProvider provideStandardFormatSkippingOptionals
	 */
	public function testStandardFormat(string $rawTag): void
	{
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(StandardLocale::class, $locale);
		self::assertSame(strtolower($rawTag), strtolower($locale->getTag()));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideStandardFormatAdding(): Generator
	{
		// Primary language
		yield 'prim lang 1' => ['pl'];
		yield 'prim lang 2' => ['prl'];

		// Primary language (without support of extension language)
		yield 'prim lang 3' => ['pril']; // Reserved
		yield 'prim lang 4' => ['primlang']; // Registered subtag

		// Extended language
		yield 'ext lang 1' => ['pl-ela'];
		yield 'ext lang 1-2' => ['prl-ela'];
		yield 'ext lang 2' => ['pl-ela-elb'];
		yield 'ext lang 3' => ['pl-ela-elb-elc'];

		// Script
		yield 'script' => ['pl-ela-elb-elc-latn'];

		// Region
		yield 'region 2 alpha' => ['pl-ela-elb-elc-latn-rg'];
		yield 'region 3 digit' => ['pl-ela-elb-elc-latn-123'];

		// Variants
		yield 'variant 5 chars' => ['pl-ela-elb-elc-latn-rg-chars'];
		yield 'variant 8 chars' => ['pl-ela-elb-elc-latn-rg-12chars3'];
		yield 'variant 1 digit 3 chars' => ['pl-ela-elb-elc-latn-rg-1chr'];
		yield 'variant repeat' => ['pl-ela-elb-elc-latn-rg-chars-12chars3-1chr'];

		// Extensions
		yield 'extension 2 chars' => ['pl-ela-elb-elc-latn-rg-chars-e-tw'];
		yield 'extension 8 chars' => ['pl-ela-elb-elc-latn-rg-chars-1-12345678'];
		yield 'extension repeat' => ['pl-ela-elb-elc-latn-rg-chars-e-tw-1-12345678'];

		// Private
		yield 'private 1 char' => ['pl-ela-elb-elc-latn-rg-chars-e-tw-x-1'];
		yield 'private 8 char' => ['pl-ela-elb-elc-latn-rg-chars-e-tw-x-eight123'];

		// All combined
		yield 'all combined' => ['pl-ela-elb-elc-latn-rg-chars-12chars3-1chr-e-tw-1-12345678-x-eight123'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideStandardFormatSkippingOptionals(): Generator
	{
		yield 'skip extlang' => ['pl-latn-rg-chars-e-tw-x-eight123'];
		yield 'skip script' => ['pl-ela-elb-elc-rg-chars-e-tw-x-eight123'];
		yield 'skip region' => ['pl-ela-elb-elc-latn-chars-e-tw-x-eight123'];
		yield 'skip variant' => ['pl-ela-elb-elc-latn-rg-e-tw-x-eight123'];
		yield 'skip extension' => ['pl-ela-elb-elc-latn-rg-chars-x-eight123'];
		yield 'skip private' => ['pl-ela-elb-elc-latn-rg-chars-e-tw'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideStandardFormatSingleOptional(): Generator
	{
		yield 'extlang' => ['pl-ela'];
		yield 'script' => ['pl-latn'];
		yield 'region' => ['pl-rg'];
		yield 'variant' => ['pl-chars'];
		yield 'extension' => ['pl-e-tw'];
		yield 'private' => ['pl-x-eight123'];
	}

	/**
	 * @dataProvider provideStandardNormalization
	 */
	public function testStandardNormalization(string $rawTag, string $normalized): void
	{
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(StandardLocale::class, $locale);
		self::assertSame(strtolower($rawTag), strtolower($locale->getTag()));
		self::assertSame($normalized, $locale->getTag());
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideStandardNormalization(): Generator
	{
		$normalized = 'pl-ela-elb-elc-Latn-RG-chars-e-tw-x-eight123';

		yield 'lower' => ['pl-ela-elb-elc-latn-rg-chars-e-tw-x-eight123', $normalized];
		yield 'upper' => ['PL-ELA-ELB-ELC-LATN-RG-CHARS-E-TW-X-EIGHT123', $normalized];
		yield 'random' => ['pl-eLA-ELb-eLc-LaTn-RG-chARs-e-TW-x-eIGHt123', $normalized];
	}

	public function testSubtagSeparatorNormalization(): void
	{
		$rawTag = 'pl_ela_elb_elc_latn_rg_chars_e_tw_x_eight123';
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(StandardLocale::class, $locale);
		self::assertSame('pl-ela-elb-elc-Latn-RG-chars-e-tw-x-eight123', $locale->getTag());
	}

	/**
	 * @dataProvider providePrivateFormat
	 */
	public function testPrivateFormat(string $rawTag): void
	{
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(PrivateLocale::class, $locale);
		self::assertSame(strtolower($rawTag), strtolower($locale->getTag()));
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function providePrivateFormat(): Generator
	{
		yield ['x-a'];
		yield ['x-12345678'];
	}

	/**
	 * @dataProvider provideGrandfatheredFormatIrregular
	 * @dataProvider provideGrandfatheredFormatRegular
	 */
	public function testGrandfatheredFormat(string $rawTag): void
	{
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(GrandfatheredLocale::class, $locale);
		self::assertSame($rawTag, $locale->getTag());
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideGrandfatheredFormatIrregular(): Generator
	{
		yield ['en-GB-oed'];
		yield ['i-ami'];
		yield ['i-bnn'];
		yield ['i-default'];
		yield ['i-enochian'];
		yield ['i-hak'];
		yield ['i-klingon'];
		yield ['i-mingo'];
		yield ['i-navajo'];
		yield ['i-pwn'];
		yield ['i-tao'];
		yield ['i-tay'];
		yield ['i-tsu'];
		yield ['sgn-BE-FR'];
		yield ['sgn-BE-NL'];
		yield ['sgn-CH-DE'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideGrandfatheredFormatRegular(): Generator
	{
		yield ['art-lojban'];
		yield ['cel-gaulish'];
		yield ['no-bok'];
		yield ['no-nyn'];
		yield ['zh-guoyu'];
		yield ['zh-hakka'];
		yield ['zh-min'];
		yield ['zh-min-nan'];
		yield ['zh-xiang'];
	}

	/**
	 * @dataProvider provideGrandfatheredNormalization
	 */
	public function testGrandfatheredNormalization(string $rawTag, string $normalized): void
	{
		$locale = $this->processor->parse($rawTag);

		self::assertInstanceOf(GrandfatheredLocale::class, $locale);
		self::assertSame(strtolower($rawTag), strtolower($locale->getTag()));
		self::assertSame($normalized, $locale->getTag());
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideGrandfatheredNormalization(): Generator
	{
		yield ['EN-GB-OED', 'en-GB-oed'];
		yield ['en-gb-oed', 'en-GB-oed'];
		yield ['sgn-be-fr', 'sgn-BE-FR'];
		yield ['SGN-be-NL', 'sgn-BE-NL'];
		yield ['SGN-ch-de', 'sgn-CH-DE'];
	}

	/**
	 * @dataProvider provideInvalidTag
	 */
	public function testInvalidTag(string $languageTag, string $message): void
	{
		$this->expectException(MalformedLanguageTag::class);
		$this->expectExceptionMessage($message);

		$this->processor->parse($languageTag);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidTag(): Generator
	{
		yield ['+ěšč', "Invalid language tag '+ěšč'."];
		yield ['_en_us', "Invalid language tag '_en_us'."];
		yield ['en-us-', "Invalid language tag 'en-us-'."];
	}

	/**
	 * @dataProvider provideInvalidOrPoorlyFormattedTag
	 */
	public function testInvalidOrPoorlyFormattedTag(string $languageTag, string $message): void
	{
		$this->expectException(MalformedLanguageTag::class);
		$this->expectExceptionMessage($message);

		$this->processor->parseAndEnsureNormalized($languageTag);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideInvalidOrPoorlyFormattedTag(): Generator
	{
		yield ['+ěšč', "Invalid language tag '+ěšč'."];
		yield ['En', "Invalid language tag 'En', use 'en' format instead."];
		yield ['EN', "Invalid language tag 'EN', use 'en' format instead."];
		yield ['EN_us', "Invalid language tag 'EN_us', use 'en-US' format instead."];
		yield ['en_us', "Invalid language tag 'en_us', use 'en-US' format instead."];
		yield ['_en_us', "Invalid language tag '_en_us'."];
		yield ['en-us-', "Invalid language tag 'en-us-'."];
	}

}
