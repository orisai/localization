<?php declare(strict_types = 1);

namespace Orisai\Localization\Logging;

use function in_array;

final class MissingResource
{

	/** @var array<string> */
	private array $languageTags;

	private string $message;

	private int $count;

	public function __construct(string $message, string $languageTag)
	{
		$this->languageTags = [$languageTag];
		$this->message = $message;
		$this->count = 1;
	}

	/**
	 * @return array<string>
	 */
	public function getLanguageTags(): array
	{
		return $this->languageTags;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	public function incrementCount(string $languageTag): void
	{
		if (!in_array($languageTag, $this->languageTags, true)) {
			$this->languageTags[] = $languageTag;
		}

		++$this->count;
	}

	public function getCount(): int
	{
		return $this->count;
	}

}
