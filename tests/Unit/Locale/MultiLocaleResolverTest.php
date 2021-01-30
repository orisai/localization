<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Locale\MultiLocaleResolver;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;

final class MultiLocaleResolverTest extends TestCase
{

	public function testNoResolver(): void
	{
		$resolver = new MultiLocaleResolver([]);
		$processor = new LocaleProcessor();

		$locales = new Locales($processor, 'en', [], []);
		self::assertNull($resolver->resolve($locales, $processor));
	}

	public function testFirstMatch(): void
	{
		$r1 = new FakeLocaleResolver('en');
		$r2 = new FakeLocaleResolver();
		$resolver = new MultiLocaleResolver([$r1, $r2]);
		$processor = new LocaleProcessor();

		$locales = new Locales($processor, 'de', ['en'], []);
		$locale = $resolver->resolve($locales, $processor);
		self::assertNotNull($locale);
		self::assertSame('en', $locale->getTag());
		self::assertTrue($r1->wasCalled());
		self::assertFalse($r2->wasCalled());
	}

	public function testSecondMatch(): void
	{
		$r1 = new FakeLocaleResolver('Fr');
		$r2 = new FakeLocaleResolver('eN');
		$resolver = new MultiLocaleResolver([$r1, $r2]);
		$processor = new LocaleProcessor();

		$locales = new Locales($processor, 'de', ['en'], []);
		$locale = $resolver->resolve($locales, $processor);
		self::assertNotNull($locale);
		self::assertSame('en', $locale->getTag());
		self::assertTrue($r1->wasCalled());
		self::assertTrue($r2->wasCalled());
	}

	public function testNoneMatch(): void
	{
		$r1 = new FakeLocaleResolver('Fr');
		$r2 = new FakeLocaleResolver('en');
		$resolver = new MultiLocaleResolver([$r1, $r2]);
		$processor = new LocaleProcessor();

		$locales = new Locales($processor, 'de', ['de', 'cs', 'jp'], []);
		self::assertNull($resolver->resolve($locales, $processor));
		self::assertTrue($r1->wasCalled());
		self::assertTrue($r2->wasCalled());
	}

}
