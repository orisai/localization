<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

final class MultiLocaleResolver implements LocaleResolver
{

	private LocaleResolverManager $resolverManager;

	public function __construct(LocaleResolverManager $resolverManager)
	{
		$this->resolverManager = $resolverManager;
	}

	public function resolve(Locales $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		foreach ($this->resolverManager->getKeys() as $resolverKey) {
			$resolver = $this->resolverManager->get($resolverKey);
			$locale = $resolver->resolve($locales, $localeProcessor);

			if ($locale !== null && $localeProcessor->isAllowed($locale, $locales)) {
				return $locale;
			}
		}

		return null;
	}

}
