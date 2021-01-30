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

	public function resolve(Locales $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		foreach ($this->resolvers as $resolver) {
			$locale = $resolver->resolve($locales, $localeProcessor);

			if ($locale !== null && $localeProcessor->isAllowed($locale, $locales)) {
				return $locale;
			}
		}

		return null;
	}

}
