<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Closure;
use Orisai\Localization\Locale\Locale;

interface Translator
{

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $languageTag = null): string;

	public function getCurrentLocale(): Locale;

	public function getDefaultLocale(): Locale;

	/**
	 * @return array<Locale>
	 */
	public function getAllowedLocales(): array;

	/**
	 * @return Closure(string, array<mixed>=, ?string=): string
	 */
	public function toFunction(): Closure;

}
