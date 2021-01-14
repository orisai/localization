<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\LocaleSet;

final class FakeLocaleResolver implements LocaleResolver
{

	private ?string $languageTag;

	private bool $wasCalled = false;

	public function __construct(?string $languageTag = null)
	{
		$this->languageTag = $languageTag;
	}

	public function resolve(LocaleSet $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		$this->wasCalled = true;

		if ($this->languageTag === null) {
			return null;
		}

		$locale = $localeProcessor->parse($this->languageTag);

		if (!$localeProcessor->isWhitelisted($locale, $locales)) {
			return null;
		}

		return $locale;
	}

	public function wasCalled(): bool
	{
		return $this->wasCalled;
	}

}
