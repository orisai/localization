<?php declare(strict_types = 1);

namespace Orisai\Localization;

/**
 * Allows LocaleConfigurator set Translator locale as not all LocaleConfigurator changes immediately appear in corresponding LocaleResolver
 * Hidden from Translator interface to prevent misuse
 */
interface ConfigurableTranslator extends Translator
{

	public function setCurrentLocale(string $currentLocale): void;

}
