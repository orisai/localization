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
	public function formatMessage(string $locale, string $pattern, array $parameters): string
	{
		$message = OriginalSymfonyMessageFormatter::formatMessage($locale, $pattern, $parameters);

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
		$formatter = OriginalSymfonyMessageFormatter::create($locale, $pattern);

		if ($formatter === null) {
			throw MalformedOrUnsupportedMessage::forPattern($pattern, $locale);
		}
	}

}
