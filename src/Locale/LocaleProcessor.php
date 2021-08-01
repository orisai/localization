<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use Orisai\Localization\Exception\MalformedLanguageTag;
use function array_merge;
use function array_unique;
use function preg_match;
use function str_replace;
use function strlen;
use function substr;
use const PREG_UNMATCHED_AS_NULL;

final class LocaleProcessor
{

	private const REGEX = /** @lang PhpRegExp */
		<<<'REGEX'
#^(?:
	(?<grandfathered>
		(?:en-GB-oed|i-(?:ami|bnn|default|enochian|hak|klingon|lux|mingo|navajo|pwn|t(?:a[oy]|su))|sgn-(?:BE-(?:FR|NL)|CH-DE)) |
		(?:art-lojban|cel-gaulish|no-(?:bok|nyn)|zh-(?:guoyu|hakka|min(?:-nan)?|xiang))
	) |
	(?:
		(?<language>([A-Za-z]{2,3}
			(?:-(?<extendedLanguage>[A-Za-z]{3}(?:-[A-Za-z]{3}){0,2}))?
		)|[A-Za-z]{4}|[A-Za-z]{5,8})
		(?:-(?<script>[A-Za-z]{4}))?
		(?:-(?<region>[A-Za-z]{2}|[0-9]{3}))?
		(?<variants>(?:-([A-Za-z0-9]{5,8}|[0-9][A-Za-z0-9]{3})+)*)
		(?<extensions>(?:-[0-9A-WY-Za-wy-z](?:-[A-Za-z0-9]{2,8})+)*)
		(?:-(?<privateSubtag>x(?:-[A-Za-z0-9]{1,8})+))?
	) |
	(?<privateLanguage>x(?:-[A-Za-z0-9]{1,8})+)
)$#ix
REGEX;

	/**
	 * BCP-47 compliant language tag parser
	 *
	 * @throws MalformedLanguageTag
	 *
	 * @see https://en.wikipedia.org/wiki/IETF_language_tag
	 * @see https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes/lang
	 * @see https://www.ietf.org/rfc/bcp/bcp47.txt
	 */
	public function parse(string $languageTag): Locale
	{
		$originalLanguageTag = $languageTag;
		// _ is not accepted by BCP-47 as separator, but it's helpful to support in non-strict tag version
		$languageTag = str_replace('_', '-', $languageTag);

		preg_match(self::REGEX, $languageTag, $matches, PREG_UNMATCHED_AS_NULL);

		if (isset($matches['language'])) {
			$language = $matches['language'];
			$extendedLanguage = $matches['extendedLanguage'] ?? null;

			$primaryLanguage = $extendedLanguage !== null
				? substr($language, 0, -(strlen($extendedLanguage) + 1))
				: $language;

			return new StandardLocale(
				$primaryLanguage,
				$extendedLanguage,
				$matches['script'] ?? null,
				$matches['region'] ?? null,
				(isset($matches['variants']) && $matches['variants'] !== '' ?
					substr($matches['variants'], 1)
					: null
				),
				(isset($matches['extensions']) && $matches['extensions'] !== '' ?
					substr($matches['extensions'], 1)
					: null
				),
				$matches['privateSubtag'] ?? null,
			);
		}

		if (isset($matches['grandfathered'])) {
			return new GrandfatheredLocale($matches['grandfathered']);
		}

		if (isset($matches['privateLanguage'])) {
			return new PrivateLocale($matches['privateLanguage']);
		}

		throw MalformedLanguageTag::forUnknownFormat($originalLanguageTag);
	}

	/**
	 * @throws MalformedLanguageTag
	 *
	 * @see parse()
	 */
	public function parseAndEnsureNormalized(string $languageTag): Locale
	{
		$locale = $this->parse($languageTag);

		if ($locale instanceof GrandfatheredLocale && ($standardLocale = $locale->getStandardLocale()) !== null) {
			throw MalformedLanguageTag::forNonNormalizedFormat($languageTag, $standardLocale->getTag());
		}

		if ($languageTag !== ($normalizedTag = $locale->getTag())) {
			throw MalformedLanguageTag::forNonNormalizedFormat($languageTag, $normalizedTag);
		}

		return $locale;
	}

	/**
	 * @param array<Locale> $locales
	 * @return array<string>
	 */
	public function localesToTagVariants(array $locales): array
	{
		$tagsByLocale = [];
		foreach ($locales as $locale) {
			$tagsByLocale[] = $locale->getTagVariants();
		}

		return array_unique(array_merge(...$tagsByLocale));
	}

	public function isAllowed(Locale $locale, Locales $locales): bool
	{
		$language = $locale->getLanguage();

		foreach ($locales->getAllowed() as $allowedLocale) {
			if ($allowedLocale->getLanguage() === $language) {
				return true;
			}
		}

		return false;
	}

}
