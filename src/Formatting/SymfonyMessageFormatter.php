<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use Orisai\Localization\Exception\MalformedOrUnsupportedMessage;
use Symfony\Polyfill\Intl\MessageFormatter\MessageFormatter as OriginalSymfonyMessageFormatter;
use function is_string;

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

		return $message;
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
