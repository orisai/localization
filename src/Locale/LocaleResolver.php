<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

interface LocaleResolver
{

	/**
	 * Returns requested locale or null if none was requested
	 * Can return locale without whitelist check, it is done by translator
	 */
	public function resolve(LocaleSet $locales, LocaleProcessor $localeProcessor): ?Locale;

}
