<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Resource\Catalogue;

final class ArrayCatalogue implements Catalogue
{

	/** @var array<array<string>> */
	private array $messages;

	/** @var array<string, array<string, int>> */
	private array $calls = [];

	/**
	 * @param array<array<string>> $messages
	 */
	public function __construct(array $messages)
	{
		$this->messages = $messages;
	}

	public function getMessage(string $message, string $languageTag): ?string
	{
		$this->calls[$languageTag][$message] = isset($this->calls[$languageTag][$message])
			? $this->calls[$languageTag][$message] + 1
			: 1;

		return $this->messages[$languageTag][$message] ?? null;
	}

	/**
	 * @return array<string, array<string, int>>
	 */
	public function getCalls(): array
	{
		return $this->calls;
	}

}
