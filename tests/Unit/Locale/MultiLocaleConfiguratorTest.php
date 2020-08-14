<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Locale;

use Orisai\Localization\Locale\MultiLocaleConfigurator;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Fixtures\FakeLocaleConfigurator;

final class MultiLocaleConfiguratorTest extends TestCase
{

	public function test(): void
	{
		/** @var array<FakeLocaleConfigurator> $configurators */
		$configurators = [
			new FakeLocaleConfigurator(),
			new FakeLocaleConfigurator(),
			new FakeLocaleConfigurator(),
		];

		$mainConfigurator = new MultiLocaleConfigurator($configurators);
		$mainConfigurator->configure('en');

		foreach ($configurators as $configurator) {
			self::assertSame('en', $configurator->getLocale());
		}
	}

}
