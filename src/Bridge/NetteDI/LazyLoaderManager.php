<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use OriNette\DI\Services\ServiceManager;
use Orisai\Localization\Resource\Loader;
use Orisai\Localization\Resource\LoaderManager;

final class LazyLoaderManager extends ServiceManager implements LoaderManager
{

	/** @var array<Loader>|null */
	private ?array $loaders = null;

	/**
	 * @return array<Loader>
	 */
	public function getAll(): array
	{
		if ($this->loaders !== null) {
			return $this->loaders;
		}

		$loaders = [];
		foreach ($this->getKeys() as $key) {
			$loaders[$key] = $this->getTypedServiceOrThrow($key, Loader::class);
		}

		return $this->loaders = $loaders;
	}

}
