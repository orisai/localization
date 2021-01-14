<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use Nette\Http\IRequest;
use Orisai\Localization\Exception\MalformedLanguageTag;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\LocaleSet;
use function array_merge;
use function explode;
use function krsort;

final class AcceptHeaderLocaleResolver implements LocaleResolver
{

	private IRequest $request;

	public function __construct(IRequest $request)
	{
		$this->request = $request;
	}

	public function resolve(LocaleSet $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		foreach ($this->getAcceptedLanguages() as $language) {
			try {
				$locale = $localeProcessor->parse($language);
			} catch (MalformedLanguageTag $exception) {
				continue;
			}

			if ($localeProcessor->isWhitelisted($locale, $locales)) {
				return $locale;
			}
		}

		return null;
	}

	/**
	 * @return array<string>
	 */
	private function getAcceptedLanguages(): array
	{
		$header = $this->request->getHeader('Accept-Language');

		if ($header === null) {
			return [];
		}

		$languagesByPriority = [];
		foreach (explode(',', $header) as $languageAndPriority) {
			$parsed = explode(';q=', $languageAndPriority, 2);
			$language = $parsed[0];
			$priority = isset($parsed[1])
				? (string) (float) $parsed[1]
				: '1.0';

			$languagesByPriority[$priority][] = $language;
		}

		krsort($languagesByPriority);

		return array_merge(...$languagesByPriority);
	}

}
