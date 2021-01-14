<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use Orisai\Localization\ConfigurableTranslator;

/**
 * Calls to some configurators (cookies) don't appear immediately in corresponding resolver
 * This bridge set locale as current translator locale to bypass the problem
 */
final class TranslatorLocaleConfigurator implements LocaleConfigurator
{

	private ConfigurableTranslator $translator;

	private LocaleConfigurator $wrappedConfigurator;

	public function __construct(ConfigurableTranslator $translator, LocaleConfigurator $wrappedConfigurator)
	{
		$this->translator = $translator;
		$this->wrappedConfigurator = $wrappedConfigurator;
	}

	public function configure(string $languageTag): void
	{
		$this->wrappedConfigurator->configure($languageTag);
		$this->translator->setCurrentLocale($languageTag);
	}

}
