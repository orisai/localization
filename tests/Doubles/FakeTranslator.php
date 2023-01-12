<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\ConfigurableTranslator;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\TranslationContracts\Translatable;

final class FakeTranslator implements ConfigurableTranslator
{

	private Locales $locales;

	private LocaleProcessor $localeProcessor;

	private Locale $currentLocale;

	public function __construct(Locales $locales, LocaleProcessor $localeProcessor)
	{
		$this->locales = $locales;

		$this->localeProcessor = $localeProcessor;
	}

	public function setCurrentLocale(string $languageTag): void
	{
		$this->currentLocale = $this->localeProcessor->parse($languageTag);
	}

	public function translate(string $message, array $parameters = [], ?string $locale = null): string
	{
		return $message;
	}

	public function translateMessage(Translatable $message, ?string $locale = null): string
	{
		return $this->translate(
			$message->getMessage(),
			$message->getParameters(),
			$locale ?? $message->getLocale(),
		);
	}

	public function getCurrentLocale(): Locale
	{
		return $this->currentLocale;
	}

	public function getDefaultLocale(): Locale
	{
		return $this->locales->getDefault();
	}

	/**
	 * @return array<Locale>
	 */
	public function getAllowedLocales(): array
	{
		return $this->locales->getAllowed();
	}

}
