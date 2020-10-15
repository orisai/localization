<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\ConfigurableTranslator;

final class FakeTranslator implements ConfigurableTranslator
{

	private string $defaultLocale;

	private string $currentLocale;

	/** @var array<string> */
	private array $whitelist;

	/**
	 * @param array<string> $whitelist
	 */
	public function __construct(string $defaultLocale, array $whitelist = [])
	{
		$this->defaultLocale = $defaultLocale;
		$this->currentLocale = $defaultLocale;
		$this->whitelist = $whitelist;
	}

	public function setCurrentLocale(string $currentLocale): void
	{
		$this->currentLocale = $currentLocale;
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $locale = null): string
	{
		return $message;
	}

	public function getCurrentLocale(): string
	{
		return $this->currentLocale;
	}

	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
	}

	/**
	 * @return array<string>
	 */
	public function getLocaleWhitelist(): array
	{
		return $this->whitelist;
	}

}
