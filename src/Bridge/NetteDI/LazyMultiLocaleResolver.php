<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\Localization\Locale\LocaleHelper;
use Orisai\Localization\Locale\LocaleResolver;
use function assert;

final class LazyMultiLocaleResolver implements LocaleResolver
{

	private Container $container;

	/** @var array<string> */
	private array $resolverServiceNames;

	/** @var array<LocaleResolver> */
	private array $resolverMap = [];

	/**
	 * @param array<string> $resolverServiceNames
	 */
	public function __construct(array $resolverServiceNames, Container $container)
	{
		$this->container = $container;
		$this->resolverServiceNames = $resolverServiceNames;
	}

	/**
	 * @param array<string> $localeWhitelist
	 */
	public function resolve(array $localeWhitelist): ?string
	{
		foreach ($this->resolverServiceNames as $resolverServiceName) {
			$resolver = $this->getResolver($resolverServiceName);
			$locale = $resolver->resolve($localeWhitelist);

			if ($locale !== null) {
				$locale = LocaleHelper::normalize($locale);

				if (LocaleHelper::isWhitelisted($locale, $localeWhitelist)) {
					return $locale;
				}
			}
		}

		return null;
	}

	private function getResolver(string $resolverServiceName): LocaleResolver
	{
		if (!isset($this->resolverMap[$resolverServiceName])) {
			$resolver = $this->container->getService($resolverServiceName);
			assert($resolver instanceof LocaleResolver);
			$this->resolverMap[$resolverServiceName] = $resolver;
		}

		return $this->resolverMap[$resolverServiceName];
	}

}
