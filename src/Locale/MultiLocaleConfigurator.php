<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

final class MultiLocaleConfigurator implements LocaleConfigurator
{

	/** @var array<LocaleConfigurator> */
	private array $configurators;

	/**
	 * @param array<LocaleConfigurator> $configurators
	 */
	public function __construct(array $configurators)
	{
		$this->configurators = $configurators;
	}

	public function configure(string $locale): void
	{
		foreach ($this->configurators as $configurator) {
			$configurator->configure($locale);
		}
	}

}
