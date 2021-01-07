<?php declare(strict_types = 1);

namespace Orisai\Localization;

use function assert;
use function function_exists;

// @codeCoverageIgnoreStart
if (!function_exists('Orisai\Localization\__')) {
	// @codeCoverageIgnoreEnd

	/**
	 * @param array<mixed> $parameters
	 */
	function __(string $message, array $parameters = [], ?string $locale = null): string
	{
		if (!isset($GLOBALS[Translator::class])) {
			$GLOBALS[Translator::class] = TranslatorHolder::getInstance()->getTranslator();
		}

		$translator = $GLOBALS[Translator::class];
		assert($translator instanceof Translator);

		return $translator->translate($message, $parameters, $locale);
	}

}
