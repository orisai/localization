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

	public function getMessage(string $message, string $locale): ?string
	{
		$this->calls[$locale][$message] = isset($this->calls[$locale][$message])
			? $this->calls[$locale][$message] + 1
			: 1;

		return $this->messages[$locale][$message] ?? null;
	}

	/**
	 * @return array<string, array<string, int>>
	 */
	public function getCalls(): array
	{
		return $this->calls;
	}

}
