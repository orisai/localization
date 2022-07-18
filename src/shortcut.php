<?php declare(strict_types = 1);

namespace Orisai\Localization;

use function function_exists;

if (!function_exists('Orisai\Localization\t')) {

	/**
	 * @param literal-string $message
	 * @param array<mixed> $parameters
	 */
	function t(string $message, array $parameters = [], ?string $languageTag = null): string
	{
		static $translator = null;

		if ($translator === null) {
			$translator = TranslatorHolder::getTranslator();
		}

		return $translator->translate($message, $parameters, $languageTag);
	}

}

if (!function_exists('Orisai\Localization\tm')) {

	function tm(TranslatableMessage $message, ?string $languageTag = null): string
	{
		static $translator = null;

		if ($translator === null) {
			$translator = TranslatorHolder::getTranslator();
		}

		return $translator->translate(
			$message->getMessage(),
			$message->getParameters(),
			$languageTag ?? $message->getLanguageTag(),
		);
	}

}
