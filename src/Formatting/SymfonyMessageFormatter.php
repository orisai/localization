<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use Orisai\Localization\Exception\MalformedOrUnsupportedMessage;
use Symfony\Polyfill\Intl\MessageFormatter\MessageFormatter as OriginalSymfonyMessageFormatter;
use function is_string;
use function str_replace;

final class SymfonyMessageFormatter implements MessageFormatter
{

	/**
	 * @param array<mixed> $parameters
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function formatMessage(string $pattern, array $parameters, string $languageTag): string
	{
		$message = OriginalSymfonyMessageFormatter::formatMessage($languageTag, $pattern, $parameters);

		if (!is_string($message)) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $languageTag);
		}

		// Replace non-breaking spaces
		// TODO - configurable, for tests-only
		return str_replace(' ', ' ', $message);
	}

	/**
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function validatePattern(string $pattern, string $languageTag): void
	{
		$formatter = OriginalSymfonyMessageFormatter::create($languageTag, $pattern);

		if ($formatter === null) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $languageTag);
		}
	}

}
