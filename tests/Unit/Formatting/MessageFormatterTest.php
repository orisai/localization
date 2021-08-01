<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Unit\Formatting;

use Generator;
use Orisai\Localization\Formatting\IntlMessageFormatter;
use Orisai\Localization\Formatting\SymfonyMessageFormatter;
use PHPUnit\Framework\TestCase;

final class MessageFormatterTest extends TestCase
{

	private static IntlMessageFormatter $intlFormatter;

	private static SymfonyMessageFormatter $symfonyFormatter;

	public static function setUpBeforeClass(): void
	{
		self::$intlFormatter = new IntlMessageFormatter();
		self::$symfonyFormatter = new SymfonyMessageFormatter();
	}

	/**
	 * @param array<mixed> $parameters
	 *
	 * @dataProvider provideChoice
	 * @dataProvider provideComplex
	 * @dataProvider provideDuration
	 * @dataProvider provideDate
	 * @dataProvider provideNumber
	 * @dataProvider provideSelect
	 * @dataProvider providePlural
	 * @dataProvider provideSpellout
	 */
	public function testIntl(string $locale, string $message, array $parameters, string $expected): void
	{
		self::assertSame($expected, self::$intlFormatter->formatMessage($message, $parameters, $locale));
		self::$intlFormatter->validatePattern($message, $locale);
	}

	/**
	 * Not supported:
	 *    - choice
	 *    - duration
	 *    - date
	 *  - spellout
	 *
	 * @param array<mixed> $parameters
	 *
	 * @dataProvider provideComplex
	 * @dataProvider provideNumber
	 * @dataProvider provideSelect
	 * @dataProvider providePlural
	 */
	public function testSymfony(string $locale, string $message, array $parameters, string $expected): void
	{
		self::assertSame($expected, self::$symfonyFormatter->formatMessage($message, $parameters, $locale));
		self::$intlFormatter->validatePattern($message, $locale);
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideNumber(): Generator
	{
		yield ['en-US', 'I have {0} apples.', [3], 'I have 3 apples.'];
		yield ['en-US', 'I have {0, number, integer} apples.', [3], 'I have 3 apples.'];
		yield ['en_US', 'I have {apples, number, integer} apples.', ['apples' => 3], 'I have 3 apples.'];

		// TODO - another languages
		//yield ['ar', 'I have {apples, number, integer} apples.', ['apples' => 3], 'I have ٣ apples.'];
		//yield ['br', 'I have {apples, number, integer} apples.', ['apples' => 3], 'I have ৩ apples.'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideDate(): Generator
	{
		yield ['en_US', 'Today is {0, date, full} - {0, time}', [1_577_531_843], 'Today is Saturday, December 28, 2019 - 11:17:23 AM'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideDuration(): Generator
	{
		yield ['en-US', 'duration: {0, duration}', [1_577_531_843], 'duration: 438,203:17:23'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideSpellout(): Generator
	{
		yield ['en-US', 'I have {0, spellout} apples', [34], 'I have thirty-four apples'];
		yield ['ar', 'لدي {0, spellout} تفاحة', [34], 'لدي أربعة و ثلاثون تفاحة'];
	}

	/**
	 * {data, plural, offsetValue =value{message}... other{message}}
	 *
	 * @return Generator<array<mixed>>
	 */
	public function providePlural(): Generator
	{
		$message1 = 'I have {apples, plural, =0{no apples} =1{one apple} other{# apples}}';

		yield ['en-US', $message1, ['apples' => 0], 'I have no apples'];
		yield ['en-US', $message1, ['apples' => 1], 'I have one apple'];
		yield ['en-US', $message1, ['apples' => 10], 'I have 10 apples'];

		$message2 = <<<'MSG'
{apples, plural,
	=0    {I have no apples}
	one   {I have one apple}
	other {I have # apples}
}
MSG;

		yield ['en-US', $message2, ['apples' => 0], 'I have no apples'];
		yield ['en-US', $message2, ['apples' => 1], 'I have one apple'];
		yield ['en-US', $message2, ['apples' => 10], 'I have 10 apples'];

		$message3 = <<<'MSG'
I have {apples, plural,
	=0    {no apples}
	one   {one apple}
	other {# apples}
}
MSG;

		yield ['en-US', $message3, ['apples' => 0], 'I have no apples'];
		yield ['en-US', $message3, ['apples' => 1], 'I have one apple'];
		yield ['en-US', $message3, ['apples' => 10], 'I have 10 apples'];

		// plural by language rules - zero, one, two, few, many, other
		// Note: this method understands rules of language and ignore cases which are not used by given language
		$message4 = 'I have {apples, plural, zero{no apples} one{one apple} two{two apples} ' .
			'few{# apples (few)} many{# apples (many)} other{# apples (other)}}';

		yield ['en-US', $message4, ['apples' => 0], 'I have 0 apples (other)'];
		yield ['en-US', $message4, ['apples' => 1], 'I have one apple'];
		yield ['en-US', $message4, ['apples' => 2], 'I have 2 apples (other)'];
		yield ['en-US', $message4, ['apples' => 3], 'I have 3 apples (other)'];
		yield ['en-US', $message4, ['apples' => 999], 'I have 999 apples (other)'];

		// TODO - symfony and ext-intl have different result
		//yield ['en-US', $message4, ['apples' => 9999], 'I have 9,999 apples (other)'];

		$message5 = 'Já mám {apples, plural, zero{žádná jablka} one{jedno jablko} two{dvě jablka} ' .
			'few{# jablka (few)} many{# jablek (many)} other{# jablek (other)}}';

		yield ['cs-CZ', $message5, ['apples' => 0], 'Já mám 0 jablek (other)'];
		yield ['cs-CZ', $message5, ['apples' => 1], 'Já mám jedno jablko'];

		// TODO - symfony chooses other instead of few
		//yield ['cs-CZ', $message5, ['apples' => 2], 'Já mám 2 jablka (few)'];
		//yield ['cs-CZ', $message5, ['apples' => 3], 'Já mám 3 jablka (few)'];
		yield ['cs-CZ', $message5, ['apples' => 999], 'Já mám 999 jablek (other)'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideChoice(): Generator
	{
		$message1 = <<<'MSG'
The value of {0,number} is {0, choice,
				 	0 #between 0 and 19|
				 	20 #between 20 and 39|
				 	40 #between 40 and 59|
				 	60 #between 60 and 79|
				 	80 #between 80 and 100|
				 	100 <more than 100}
MSG;

		yield ['en-US', $message1, [60], 'The value of 60 is between 60 and 79'];
		yield ['en-US', $message1, [85], 'The value of 85 is between 80 and 100'];

		// Out of range choose the closest
		yield ['en-US', $message1, [-30], 'The value of -30 is between 0 and 19'];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideSelect(): Generator
	{
		yield [
			'en-US',
			'{gender, select, female {She has some apples} male {He has some apples.}other {It has some apples.}}',
			['gender' => 'female'],
			'She has some apples',
		];
	}

	/**
	 * @return Generator<array<mixed>>
	 */
	public function provideComplex(): Generator
	{
		$complexPattern
			= <<<'MSG'
{gender_of_host, select,
    female {{num_guests, plural, offset:1
        =0    {{host} does not give a party.}
        =1    {{host} invites {guest} to her party.}
        =2    {{host} invites {guest} and one other person to her party.}
        other {{host} invites {guest} and # other people to her party.}
    }}
    male {{num_guests, plural, offset:1
        =0    {{host} does not give a party.}
        =1    {{host} invites {guest} to his party.}
        =2    {{host} invites {guest} and one other person to his party.}
        other {{host} invites {guest} and # other people to his party.}
    }}
    other {{num_guests, plural, offset:1
        =0    {{host} does not give a party.}
        =1    {{host} invites {guest} to their party.}
        =2    {{host} invites {guest} and one other person to their party.}
        other {{host} invites {guest} and # other people to their party.}
    }}
}
MSG;

		yield [
			'en-US',
			$complexPattern,
			['gender_of_host' => 'female', 'num_guests' => 5, 'host' => 'Hanae', 'guest' => 'Younes'],
			'Hanae invites Younes and 4 other people to her party.',
		];

		yield [
			'en-US',
			$complexPattern,
			['gender_of_host' => 'female', 'num_guests' => 0, 'host' => 'Hanae', 'guest' => 'Younes'],
			'Hanae does not give a party.',
		];

		yield [
			'en-US',
			$complexPattern,
			['gender_of_host' => 'other', 'num_guests' => 1, 'host' => 'Hanae and John', 'guest' => 'Younes'],
			'Hanae and John invites Younes to their party.',
		];

		yield [
			'en-US',
			$complexPattern,
			['gender_of_host' => 'male', 'num_guests' => 2, 'host' => 'John', 'guest' => 'Mr. Bean'],
			'John invites Mr. Bean and one other person to his party.',
		];
	}

}
