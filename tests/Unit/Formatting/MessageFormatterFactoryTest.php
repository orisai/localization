<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Formatting;

use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Formatting\MessageFormatterFactory;
use Orisai\Localization\Formatting\SymfonyMessageFormatter;
use PHPUnit\Framework\TestCase;

final class MessageFormatterFactoryTest extends TestCase
{

	public function test(): void
	{
		$formatter = MessageFormatterFactory::create();

		self::assertTrue($formatter instanceof IntlMessageFormatter || $formatter instanceof SymfonyMessageFormatter);
	}

}
