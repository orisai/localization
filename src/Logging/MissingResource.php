<?php declare(strict_types = 1);

namespace Orisai\Localization\Logging;

use function in_array;

final class MissingResource
{

	/** @var array<string> */
	private array $locales;

	private string $message;

	private int $count;

	public function __construct(string $locale, string $message)
	{
		$this->locales = [$locale];
		$this->message = $message;
		$this->count = 1;
	}

	/**
	 * @return array<string>
	 */
	public function getLocales(): array
	{
		return $this->locales;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function incrementCount(string $locale): void
	{
		if (!in_array($locale, $this->locales, true)) {
			$this->locales[] = $locale;
		}

		++$this->count;
	}

	public function getCount(): int
	{
		return $this->count;
	}

}
