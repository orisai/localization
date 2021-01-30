<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\TranslatableMessage;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;

final class TranslatableMessageTest extends TestCase
{

	public function test(): void
	{
		$processor = new LocaleProcessor();
		$translator = new DefaultTranslator(
			new Locales($processor, 'en', ['cs'], []),
			new FakeLocaleResolver(),
			new ArrayCatalogue([
				'en' => [
					'message' => 'I have {apples} apples.',
				],
				'cs' => [
					'message' => 'Já mám {apples} jablka.',
				],
			]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
			$processor,
		);

		$message = new TranslatableMessage('message', ['apples' => 3]);

		self::assertSame('message', $message->getMessage());
		self::assertSame(['apples' => 3], $message->getParameters());

		self::assertSame('I have 3 apples.', $message->translate($translator));
		self::assertSame('Já mám 3 jablka.', $message->translate($translator, 'cs-CZ'));
		self::assertSame('I have 3 apples.', $translator->translate($message->getMessage(), $message->getParameters()));
	}

}
