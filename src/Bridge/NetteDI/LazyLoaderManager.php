<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use OriNette\DI\Services\CachedServiceManager;
use Orisai\Localization\Resource\Loader;
use Orisai\Localization\Resource\LoaderManager;

final class LazyLoaderManager extends CachedServiceManager implements LoaderManager
{

	/**
	 * @param int|string $key
	 */
	private function get($key): Loader
	{
		$service = $this->getService($key);

		if ($service === null) {
			$this->throwMissingService($key, Loader::class);
		}

		if (!$service instanceof Loader) {
			$this->throwInvalidServiceType($key, Loader::class, $service);
		}

		return $service;
	}

	/**
	 * @return array<Loader>
	 */
	public function getAll(): array
	{
		$loaders = [];
		foreach ($this->getKeys() as $key) {
			$loaders[$key] = $this->get($key);
		}

		return $loaders;
	}

}
