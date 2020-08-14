<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use MessageFormatter as OriginalIntlMessageFormatter;
use Orisai\Exceptions\Logic\InvalidState;
use Symfony\Polyfill\Intl\MessageFormatter\MessageFormatter as OriginalSymfonyMessageFormatter;
use function class_exists;
use function sprintf;

final class MessageFormatterFactory
{

	public static function create(): MessageFormatter
	{
		if (class_exists(OriginalIntlMessageFormatter::class)) {
			return new IntlMessageFormatter();
		}

		if (class_exists(OriginalSymfonyMessageFormatter::class)) {
			return new SymfonyMessageFormatter();
		}

		throw InvalidState::create()
			->withMessage(sprintf(
				'Cannot find compatible "%s", please install "ext-intl" or "symfony/polyfill-intl-messageformatter" or create your own.',
				MessageFormatter::class,
			));
	}

}
