<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\Exception\LanguageNotAllowed;
use Orisai\Localization\Formatting\MessageFormatter;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\Locales;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\Catalogue;
use Orisai\TranslationContracts\Translatable;
use function array_merge;
use function array_unique;
use function sprintf;

final class DefaultTranslator implements ConfigurableTranslator
{

	private Catalogue $catalogue;

	private LocaleResolver $localeResolver;

	private MessageFormatter $messageFormatter;

	private TranslationsLogger $logger;

	private LocaleProcessor $localeProcessor;

	private Locales $locales;

	private ?Locale $currentLocale = null;

	/** @var array<array<string>> */
	private array $possibleLanguageTags = [];

	public function __construct(
		Locales $locales,
		LocaleResolver $localeResolver,
		Catalogue $catalogue,
		MessageFormatter $messageFormatter,
		TranslationsLogger $logger,
		LocaleProcessor $localeProcessor
	)
	{
		$this->locales = $locales;

		$this->localeResolver = $localeResolver;
		$this->catalogue = $catalogue;
		$this->messageFormatter = $messageFormatter;
		$this->logger = $logger;
		$this->localeProcessor = $localeProcessor;
	}

	public function translate(string $id, array $parameters = [], ?string $locale = null): string
	{
		$localeObj = $locale !== null
			? $this->checkValidAndAllowed($locale)
			: $this->getCurrentLocale();
		$locale = $localeObj->getTag();

		$translatedMessage = null;
		$translatedMessageLanguageTag = $locale;

		foreach ($this->getPossibleLanguageTags($localeObj) as $possibleLanguageTag) {
			$translatedMessage = $this->catalogue->getMessage($id, $possibleLanguageTag);

			if ($translatedMessage !== null) {
				$translatedMessageLanguageTag = $possibleLanguageTag;

				break;
			}
		}

		if ($translatedMessage === null) {
			$this->logger->addMissingResource($id, $locale);

			return $id;
		}

		return $this->messageFormatter->formatMessage($translatedMessage, $parameters, $translatedMessageLanguageTag);
	}

	public function translateMessage(Translatable $message, ?string $locale = null): string
	{
		return $this->translate(
			$message->getId(),
			$message->getParameters(),
			$locale ?? $message->getLocale(),
		);
	}

	public function getDefaultLocale(): Locale
	{
		return $this->locales->getDefault();
	}

	/**
	 * @return array<Locale>
	 */
	public function getAllowedLocales(): array
	{
		return $this->locales->getAllowed();
	}

	public function setCurrentLocale(string $locale): void
	{
		if ($this->currentLocale !== null) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Current locale already set. Ensure you call %s() only once and before translator is first used.',
					__METHOD__,
				));
		}

		$currentLocale = $this->checkValidAndAllowed($locale);
		$this->currentLocale = $currentLocale;
	}

	public function getCurrentLocale(): Locale
	{
		if ($this->currentLocale !== null) {
			return $this->currentLocale;
		}

		$resolved = $this->localeResolver->resolve($this->locales, $this->localeProcessor);

		if ($resolved !== null && $this->localeProcessor->isAllowed($resolved, $this->locales)) {
			return $this->currentLocale = $resolved;
		}

		return $this->currentLocale = $this->locales->getDefault();
	}

	private function checkValidAndAllowed(string $languageTag): Locale
	{
		$locale = $this->localeProcessor->parseAndEnsureNormalized($languageTag);

		if (!$this->localeProcessor->isAllowed($locale, $this->locales)) {
			throw LanguageNotAllowed::forLocales($locale, $this->locales);
		}

		return $locale;
	}

	/**
	 * @return array<string>
	 */
	private function getPossibleLanguageTags(Locale $locale): array
	{
		$languageTag = $locale->getTag();

		if (isset($this->possibleLanguageTags[$languageTag])) {
			return $this->possibleLanguageTags[$languageTag];
		}

		$listByLocale = [];
		$listByLocale[] = $locale->getTagVariants();

		$fallbacks = $this->locales->getFallbacks();
		$language = $locale->getLanguage();

		// Add locale from fallback
		if (isset($fallbacks[$language])) {
			$listByLocale[] = $fallbacks[$language]->getTagVariants();
		}

		// Add default locale
		$defaultLocale = $this->locales->getDefault();
		if ($languageTag !== $defaultLocale->getTag()) {
			$listByLocale[] = $defaultLocale->getTagVariants();
		}

		return $this->possibleLanguageTags[$languageTag] = array_unique(array_merge(...$listByLocale));
	}

}
