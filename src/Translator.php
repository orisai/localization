<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Localization\Locale\Locale;

interface Translator
{

	/**
	 * @param literal-string $message
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $languageTag = null): string;

	public function translateMessage(TranslatableMessage $message, ?string $languageTag = null): string;

	public function getCurrentLocale(): Locale;

	public function getDefaultLocale(): Locale;

	/**
	 * @return array<Locale>
	 */
	public function getAllowedLocales(): array;

}
