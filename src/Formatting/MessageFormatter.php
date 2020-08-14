<?php declare(strict_types = 1);

namespace Orisai\Localization\Formatting;

use Orisai\Localization\Exception\MalformedOrUnsupportedMessage;

interface MessageFormatter
{

	/**
	 * @param array<mixed> $parameters
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function formatMessage(string $locale, string $pattern, array $parameters): string;

	/**
	 * @throws MalformedOrUnsupportedMessage
	 */
	public function validatePattern(string $locale, string $pattern): void;

}
