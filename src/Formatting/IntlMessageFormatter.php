<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use MessageFormatter as OriginalIntlMessageFormatter;
use Orisai\Localization\Exception\MalformedOrUnsupportedMessage;
use function is_string;

final class IntlMessageFormatter implements MessageFormatter
{

	/**
	 * @param array<mixed> $parameters
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function formatMessage(string $locale, string $pattern, array $parameters): string
	{
		$message = OriginalIntlMessageFormatter::formatMessage($locale, $pattern, $parameters);

		if (!is_string($message)) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $locale);
		}

		return $message;
	}

	/**
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function validatePattern(string $locale, string $pattern): void
	{
		$formatter = OriginalIntlMessageFormatter::create($locale, $pattern);

		if ($formatter === null) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $locale);
		}
	}

}
