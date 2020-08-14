<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Resource;

use Orisai\Localization\Resource\MultiLoader;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Fixtures\ArrayLoader;

final class MultiLoaderTest extends TestCase
{

	public function testMerging(): void
	{
		$loader = new MultiLoader([
			new ArrayLoader([
				'en-US' => [
					'key1' => 'first',
					'key2' => 'first',
					'key3' => 'first',
				],
			]),
			new ArrayLoader([
				'en-US' => [
					'key3' => 'second',
					'key4' => 'second',
				],
				'en' => [
					'key1' => 'second',
				],
			]),
			new ArrayLoader([
				'en-US' => [
					'key4' => 'third',
					'key5' => 'third',
					'key1' => 'third',
				],
			]),
		]);

		self::assertSame(
			[
				'key1' => 'third',
				'key2' => 'first',
				'key3' => 'second',
				'key4' => 'third',
				'key5' => 'third',
			],
			$loader->loadAllMessages('en-US'),
		);

		self::assertSame(
			[
				'key1' => 'second',
			],
			$loader->loadAllMessages('en'),
		);

		self::assertSame(
			[],
			$loader->loadAllMessages('cs'),
		);
	}

	public function testNoLoaders(): void
	{
		$loader = new MultiLoader([]);

		self::assertSame(
			[],
			$loader->loadAllMessages('en'),
		);
	}

}
