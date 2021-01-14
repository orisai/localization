<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteCaching;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Orisai\Localization\Resource\Catalogue;
use Orisai\Localization\Resource\Loader;

final class CachedCatalogue implements Catalogue
{

	private const CACHE_KEY = 'orisai.localization';

	private Loader $loader;

	private Cache $cache;

	/** @var array<array<bool>> Map of message => locale which were not found in any loader */
	private array $missingTranslationLocaleMap = [];

	public function __construct(Loader $loader, IStorage $storage)
	{
		$this->loader = $loader;
		$this->cache = new Cache($storage, self::CACHE_KEY);
	}

	public function getMessage(string $message, string $languageTag): ?string
	{
		$cache = $this->cache->derive('.' . $languageTag);

		// Try get translation from cache
		$translated = $this->cache->load($message);

		// Translation is already cached
		if ($translated !== null) {
			return $translated;
		}

		// Loader don't contain translation for given message with requested language, skip lookup
		if ($this->missingTranslationLocaleMap[$message][$languageTag] ?? false) {
			return null;
		}

		// Load all translations for given locale
		foreach ($this->loader->loadAllMessages($languageTag) as $key => $translation) {
			// Try to load message and save only if not cached yet
			$cache->load($key, static fn (): string => $translation);

			// Loaded key is same as requested message, use it
			if ($key === $message) {
				$translated = $translation;
			}
		}

		// Translation found, return it
		if ($translated !== null) {
			return $translated;
		}

		// Loader don't contain translation for given message with requested language, skip at next run
		$this->missingTranslationLocaleMap[$message][$languageTag] = true;

		return null;
	}

}
