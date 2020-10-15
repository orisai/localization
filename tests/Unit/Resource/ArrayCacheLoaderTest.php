<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Resource;

use Orisai\Localization\Resource\ArrayCacheLoader;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayLoader;

final class ArrayCacheLoaderTest extends TestCase
{

	public function test(): void
	{
		$arrayLoader = new ArrayLoader([
			'en' => [],
			'en-US' => ['key' => 'translation'],
		]);

		$loader = new ArrayCacheLoader($arrayLoader);
		$loader->loadAllMessages('cs');
		$loader->loadAllMessages('cs');

		$loader->loadAllMessages('en');
		$loader->loadAllMessages('en');
		$loader->loadAllMessages('en');

		$loader->loadAllMessages('en-US');
		$loader->loadAllMessages('en-US');

		$loader->loadAllMessages('en-GB');

		self::assertSame(
			[
				'cs' => 1,
				'en' => 1,
				'en-US' => 1,
				'en-GB' => 1,
			],
			$arrayLoader->getCalls(),
		);
	}

}
