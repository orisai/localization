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
		return $this->getTypedServiceOrThrow($key, LocaleResolver::class);
	}

	/**
	 * @return array<int, int|string>
	 */
	public function getKeys(): array
	{
		return parent::getKeys();
	}

}
