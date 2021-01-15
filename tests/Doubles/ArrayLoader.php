<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Resource\Loader;

final class ArrayLoader implements Loader
{

	/** @var array<string, array<string, string>> */
	private array $messages;

	/** @var array<int> */
	private array $calls = [];

	/**
	 * @param array<string, array<string, string>> $messages
	 */
	public function __construct(array $messages)
	{
		$this->messages = $messages;
	}

	/**
	 * @return array<string, string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		$this->calls[$languageTag] = isset($this->calls[$languageTag])
			? $this->calls[$languageTag] + 1
			: 1;

		return $this->messages[$languageTag] ?? [];
	}

	/**
	 * @return array<int>
	 */
	public function getCalls(): array
	{
		return $this->calls;
	}

}
