<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

final class MultiLocaleResolver implements LocaleResolver
{

	/** @var array<LocaleResolver> */
	private array $resolvers;

	/**
	 * @param array<LocaleResolver> $resolvers
	 */
	public function __construct(array $resolvers)
	{
		$this->resolvers = $resolvers;
	}

	/**
	 * @param array<string> $localeWhitelist
	 */
	public function resolve(array $localeWhitelist): ?string
	{
		foreach ($this->resolvers as $resolver) {
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

}
