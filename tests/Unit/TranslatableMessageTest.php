<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Localization\TranslatableMessage;
use PHPUnit\Framework\TestCase;

final class TranslatableMessageTest extends TestCase
{

	public function testBasic(): void
	{
		$message = new TranslatableMessage('message');

		self::assertSame('message', $message->getMessage());
		self::assertSame([], $message->getParameters());
		self::assertNull($message->getLanguageTag());
	}

	public function testSpecifyLanguage(): void
	{
		$message = new TranslatableMessage('message', ['apples' => 3], 'cs-CZ');

		self::assertSame('message', $message->getMessage());
		self::assertSame(['apples' => 3], $message->getParameters());
		self::assertSame($message->getLanguageTag(), 'cs-CZ');
	}

}
