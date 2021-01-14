<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

interface LocaleConfigurator
{

	/**
	 * Stores locale so it could be restored by corresponding LocaleResolver
	 * Can also set locale as current Translator locale
	 * Should be called only with valid, normalized locale
	 *
	 * @see TranslatorLocaleConfigurator sets Translator curent locale
	 * @see MultiLocaleConfigurator calls multiple configurators
	 */
	public function configure(string $languageTag): void;

}
