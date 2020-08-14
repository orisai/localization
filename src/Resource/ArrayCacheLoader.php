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
	 * @return array<string>
	 */
	public function loadAllMessages(string $locale): array
	{
		if (isset($this->cache[$locale])) {
			return $this->cache[$locale];
		}

		return $this->cache[$locale] = $this->loader->loadAllMessages($locale);
	}

}
