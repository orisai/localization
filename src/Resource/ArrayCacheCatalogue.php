<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

use function array_key_exists;

/**
 * Ensures wrapped catalogue is called only once for every message
 */
final class ArrayCacheCatalogue implements Catalogue
{

	private Catalogue $catalogue;

	/** @var array<array<(string|null)>> */
	private array $cache = [];

	public function __construct(Catalogue $catalogue)
	{
		$this->catalogue = $catalogue;
	}

	public function getMessage(string $message, string $languageTag): ?string
	{
		if (isset($this->cache[$message]) && array_key_exists($languageTag, $this->cache[$message])) {
			return $this->cache[$message][$languageTag];
		}

		return $this->cache[$message][$languageTag] = $this->catalogue->getMessage($message, $languageTag);
	}

}
