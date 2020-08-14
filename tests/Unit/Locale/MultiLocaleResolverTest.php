<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Locale\MultiLocaleResolver;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Fixtures\FakeLocaleResolver;

final class MultiLocaleResolverTest extends TestCase
{

	public function testNoResolver(): void
	{
		$resolver = new MultiLocaleResolver([]);

		self::assertNull($resolver->resolve([]));
	}

	public function testFirstMatch(): void
	{
		$r1 = new FakeLocaleResolver('en');
		$r2 = new FakeLocaleResolver();
		$resolver = new MultiLocaleResolver([$r1, $r2]);

		self::assertSame('en', $resolver->resolve(['en']));
		self::assertTrue($r1->wasCalled());
		self::assertFalse($r2->wasCalled());
	}

	public function testSecondMatch(): void
	{
		$r1 = new FakeLocaleResolver('Fr');
		$r2 = new FakeLocaleResolver('eN');
		$resolver = new MultiLocaleResolver([$r1, $r2]);

		self::assertSame('en', $resolver->resolve(['en']));
		self::assertTrue($r1->wasCalled());
		self::assertTrue($r2->wasCalled());
	}

	public function testNoneMatch(): void
	{
		$r1 = new FakeLocaleResolver('Fr');
		$r2 = new FakeLocaleResolver('en');
		$resolver = new MultiLocaleResolver([$r1, $r2]);

		self::assertNull($resolver->resolve(['de', 'cs', 'en-US']));
		self::assertTrue($r1->wasCalled());
		self::assertTrue($r2->wasCalled());
	}

}
