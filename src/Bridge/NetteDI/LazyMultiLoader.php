<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\Localization\Resource\Loader;
use function array_merge;
use function assert;

final class LazyMultiLoader implements Loader
{

	/** @var array<string> */
	private array $loaderServiceNames;

	private Container $container;

	/** @var array<Loader> */
	private array $loaderMap = [];

	/**
	 * @param array<string> $loaderServiceNames
	 */
	public function __construct(array $loaderServiceNames, Container $container)
	{
		$this->loaderServiceNames = $loaderServiceNames;
		$this->container = $container;
	}

	/**
	 * @return array<string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		$messagesByLoader = [];

		foreach ($this->loaderServiceNames as $loaderServiceName) {
			$loader = $this->getLoader($loaderServiceName);

			$messagesByLoader[] = $loader->loadAllMessages($languageTag);
		}

		return $messagesByLoader === [] ? [] : array_merge(...$messagesByLoader);
	}

	private function getLoader(string $loaderServiceName): Loader
	{
		if (!isset($this->loaderMap[$loaderServiceName])) {
			$loader = $this->container->getService($loaderServiceName);
			assert($loader instanceof Loader);
			$this->loaderMap[$loaderServiceName] = $loader;
		}

		return $this->loaderMap[$loaderServiceName];
	}

}
