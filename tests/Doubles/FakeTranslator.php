<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Closure;
use Orisai\Localization\ConfigurableTranslator;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\TranslatableMessage;

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

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $languageTag = null): string
	{
		return $message;
	}

	public function translateMessage(TranslatableMessage $message, ?string $languageTag = null): string
	{
		return $this->translate(
			$message->getMessage(),
			$message->getParameters(),
			$languageTag ?? $message->getLanguageTag(),
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

	public function toFunction(): Closure
	{
		return fn (string $message, array $parameters = [], ?string $languageTag = null): string => $this->translate(
			$message,
			$parameters,
			$languageTag,
		);
	}

}
