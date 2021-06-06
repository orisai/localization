<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use OriNette\DI\Services\ServiceManager;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\LocaleResolverManager;

final class LazyLocaleResolverManager extends ServiceManager implements LocaleResolverManager
{

	/**
	 * @param int|string $key
	 */
	public function get($key): LocaleResolver
	{
		$service = $this->getService($key);

		if ($service === null) {
			$this->throwMissingService($key, LocaleResolver::class);
		}

		if (!$service instanceof LocaleResolver) {
			$this->throwInvalidServiceType($key, LocaleResolver::class, $service);
		}

		return $service;
	}

	/**
	 * @return array<int|string>
	 */
	public function getKeys(): array
	{
		return parent::getKeys();
	}

}
