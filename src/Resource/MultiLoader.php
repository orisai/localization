<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

use function array_merge;

final class MultiLoader implements Loader
{

	private LoaderManager $loaderManager;

	public function __construct(LoaderManager $loaderManager)
	{
		$this->loaderManager = $loaderManager;
	}

	/**
	 * @return array<string, string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		$messagesByLoader = [];

		foreach ($this->loaderManager->getAll() as $loader) {
			$messagesByLoader[] = $loader->loadAllMessages($languageTag);
		}

		return $messagesByLoader === [] ? [] : array_merge(...$messagesByLoader);
	}

}
