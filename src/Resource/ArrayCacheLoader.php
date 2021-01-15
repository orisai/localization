<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

/**
 * Ensures wrapped loader is called only once for every used locale
 */
final class ArrayCacheLoader implements Loader
{

	private Loader $loader;

	/** @var array<array<string>> */
	private array $cache = [];

	public function __construct(Loader $loader)
	{
		$this->loader = $loader;
	}

	/**
	 * @return array<string, string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		if (isset($this->cache[$languageTag])) {
			return $this->cache[$languageTag];
		}

		return $this->cache[$languageTag] = $this->loader->loadAllMessages($languageTag);
	}

}
