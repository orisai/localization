<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

final class DefaultLoaderManager implements LoaderManager
{

	/** @var array<int|string, Loader> */
	private array $loaders;

	/**
	 * @param array<int|string, Loader> $loaders
	 */
	public function __construct(array $loaders)
	{
		$this->loaders = $loaders;
	}

	/**
	 * @return array<Loader>
	 */
	public function getAll(): array
	{
		return $this->loaders;
	}

}
