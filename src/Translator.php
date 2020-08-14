<?php declare(strict_types = 1);

namespace Orisai\Localization;

interface Translator
{

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $locale = null): string;

	public function getCurrentLocale(): string;

	public function getDefaultLocale(): string;

	/**
	 * @return array<string>
	 */
	public function getLocaleWhitelist(): array;

}
