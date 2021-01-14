<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Locale\LocaleConfigurator;

final class FakeLocaleConfigurator implements LocaleConfigurator
{

	private ?string $languageTag = null;

	public function configure(string $languageTag): void
	{
		$this->languageTag = $languageTag;
	}

	public function getLanguageTag(): string
	{
		return $this->languageTag;
	}

}
