<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\DefaultTranslator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\TranslatorHolder;
use PHPUnit\Framework\TestCase;
use Tests\Orisai\Localization\Doubles\ArrayCatalogue;
use Tests\Orisai\Localization\Doubles\FakeLocaleResolver;

/**
 * @runTestsInSeparateProcesses
 */
final class TranslatorHolderTest extends TestCase
{

	public function testOk(): void
	{
		$translator = DefaultTranslator::fromRawLocales(
			'en',
			[],
			[],
			new FakeLocaleResolver(),
			new ArrayCatalogue([]),
			new IntlMessageFormatter(),
			new TranslationsLogger(),
		);
		TranslatorHolder::setTranslator($translator);

		self::assertInstanceOf(DefaultTranslator::class, TranslatorHolder::getInstance()->getTranslator());
	}

	public function testNotConfigured(): void
	{
		$this->expectException(InvalidState::class);
		$this->expectExceptionMessage(
			'Call Orisai\Localization\TranslatorHolder::setTranslator() to use Orisai\Localization\TranslatorHolder::getInstance()',
		);

		TranslatorHolder::getInstance()->getTranslator();
	}

}
