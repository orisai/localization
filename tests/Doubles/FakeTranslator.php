<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\ConfigurableTranslator;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleSet;

final class FakeTranslator implements ConfigurableTranslator
{

	private LocaleSet $locales;
	private LocaleProcessor $localeProcessor;

	private Locale $currentLocale;

	public function __construct(LocaleSet $locales, LocaleProcessor $localeProcessor)
	{
		$this->locales = $locales;

		$this->localeProcessor = $localeProcessor;
	}

	public function setCurrentLocale(string $languageTag): void
	{
		$this->currentLocale = $this->localeProcessor->parse($languageTag);
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $languageTag = null): string
	{
		return $message;
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

	public function toFunction(): callable
	{
		return fn (string $message, array $parameters = [], ?string $languageTag = null): string => $this->translate(
			$message,
			$parameters,
			$languageTag,
		);
	}

}
