<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

interface LocaleResolverManager
{

	/**
	 * @param int|string $key
	 */
	public function get($key): LocaleResolver;

	/**
	 * @return array<int|string>
	 */
	public function getKeys(): array;

}
