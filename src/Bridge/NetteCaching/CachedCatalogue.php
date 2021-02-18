<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteCaching;

use Nette\Caching\Cache;
use Nette\Caching\IStorage;
use Orisai\Localization\Resource\Catalogue;
use Orisai\Localization\Resource\Loader;
use function array_key_exists;
use function assert;
use function is_array;

final class CachedCatalogue implements Catalogue
{

	private const CACHE_KEY = 'orisai.localization';

	private Loader $loader;

	private Cache $cache;

	private bool $debugMode;

	/** @var array<string, array<string>> */
	private array $arrayCache = [];

	/** @var array<string, null> */
	private array $loadedFromLoader = [];

	public function __construct(Loader $loader, IStorage $storage, bool $debugMode)
	{
		$this->loader = $loader;
		$this->cache = new Cache($storage, self::CACHE_KEY);
		$this->debugMode = $debugMode;
	}

	public function getMessage(string $message, string $languageTag): ?string
	{
		$messages = $this->arrayCache[$languageTag] ?? null;

		if ($messages === null) {
			$messages = $this->cache->load(
				$languageTag,
				function () use ($languageTag): array {
					$this->loadedFromLoader[$languageTag] = null;

					return $this->loader->loadAllMessages($languageTag);
				},
			);
			assert(is_array($messages));

			$this->arrayCache[$languageTag] = $messages;
		}

		$translation = $messages[$message] ?? null;

		if ($translation === null && $this->debugMode && !array_key_exists($languageTag, $this->loadedFromLoader)) {
			$this->loadedFromLoader[$languageTag] = null;

			$messages = $this->loader->loadAllMessages($languageTag);
			$this->arrayCache[$languageTag] = $messages;
			$this->cache->save($languageTag, $messages);

			$translation = $messages[$message] ?? null;
		}

		return $translation;
	}

}
