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
	public function formatMessage(string $pattern, array $parameters, string $languageTag): string
	{
		$message = OriginalIntlMessageFormatter::formatMessage($languageTag, $pattern, $parameters);

		if (!is_string($message)) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $languageTag);
		}

		return $message;
	}

	/**
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function validatePattern(string $pattern, string $languageTag): void
	{
		$formatter = OriginalIntlMessageFormatter::create($languageTag, $pattern);

		if ($formatter === null) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $languageTag);
		}
	}

}
