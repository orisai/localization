<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Locale\LocaleConfigurator;

final class FakeLocaleConfigurator implements LocaleConfigurator
{

	private ?string $locale = null;

	public function configure(string $locale): void
	{
		$this->locale = $locale;
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

}
