<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

use function array_merge;

final class MultiLoader implements Loader
{

	/** @var array<Loader> */
	private array $loaders;

	/**
	 * @param array<Loader> $loaders
	 */
	public function __construct(array $loaders)
	{
		$this->loaders = $loaders;
	}

	/**
	 * @return array<string>
	 */
	public function loadAllMessages(string $locale): array
	{
		$messagesByLoader = [];

		foreach ($this->loaders as $loader) {
			$messagesByLoader[] = $loader->loadAllMessages($locale);
		}

		return $messagesByLoader === [] ? [] : array_merge(...$messagesByLoader);
	}

}
