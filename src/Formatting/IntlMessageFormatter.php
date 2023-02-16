<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use MessageFormatter as OriginalIntlMessageFormatter;
use Orisai\Localization\Exception\MalformedOrUnsupportedMessage;
use function is_string;
use function str_replace;
use const PHP_OS;

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

		// Some specific versions of intl extension on macOS throw garbage into result string
		if (PHP_OS === 'Darwin') {
			$message = str_replace(' ', ' ', $message);
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
