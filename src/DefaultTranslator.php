<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Exceptions\Logic\InvalidState;
use Orisai\Localization\Exception\LocaleNotWhitelisted;
use Orisai\Localization\Formatting\MessageFormatter;
use Orisai\Localization\Locale\LocaleHelper;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Resource\Catalogue;
use function array_unique;
use function in_array;
use function sprintf;

final class DefaultTranslator implements ConfigurableTranslator
{

	private Catalogue $catalogue;
	private LocaleResolver $localeResolver;
	private MessageFormatter $messageFormatter;
	private TranslationsLogger $logger;

	private string $defaultLocale;
	private ?string $currentLocale = null;

	/** @var array<string> */
	private array $localeWhitelist;

	/** @var array<string> */
	private array $fallbackLocales;

	/** @var array<array<string>> */
	private array $possibleLocales = [];

	/**
	 * @param array<string> $localeWhiteList
	 * @param array<string> $fallbackLocales
	 */
	private function __construct(
		string $defaultLocale,
		array $localeWhiteList,
		array $fallbackLocales,
		LocaleResolver $localeResolver,
		Catalogue $catalogue,
		MessageFormatter $messageFormatter,
		TranslationsLogger $logger
	)
	{
		if (!in_array($defaultLocale, $localeWhiteList, true)) {
			$localeWhiteList[] = $defaultLocale;
		}

		$this->defaultLocale = $defaultLocale;
		$this->localeWhitelist = $localeWhiteList;
		$this->fallbackLocales = $fallbackLocales;
		$this->localeResolver = $localeResolver;
		$this->catalogue = $catalogue;
		$this->messageFormatter = $messageFormatter;
		$this->logger = $logger;
	}

	/**
	 * @param array<string> $localeWhiteList
	 * @param array<string> $fallbackLocales
	 */
	public static function fromValidLocales(
		string $defaultLocale,
		array $localeWhiteList,
		array $fallbackLocales,
		LocaleResolver $localeResolver,
		Catalogue $catalogue,
		MessageFormatter $messageFormatter,
		TranslationsLogger $logger
	): self
	{
		return new self(
			$defaultLocale,
			$localeWhiteList,
			$fallbackLocales,
			$localeResolver,
			$catalogue,
			$messageFormatter,
			$logger,
		);
	}

	/**
	 * @param array<string> $localeWhiteList
	 * @param array<string> $fallbackLocales
	 */
	public static function fromRawLocales(
		string $defaultLocale,
		array $localeWhiteList,
		array $fallbackLocales,
		LocaleResolver $localeResolver,
		Catalogue $catalogue,
		MessageFormatter $messageFormatter,
		TranslationsLogger $logger
	): self
	{
		LocaleHelper::validate($defaultLocale);

		foreach ($localeWhiteList as $whitelistedLocale) {
			LocaleHelper::validate($whitelistedLocale);
		}

		foreach ($fallbackLocales as $requestedLocale => $fallbackLocale) {
			LocaleHelper::validate($requestedLocale);
			LocaleHelper::validate($fallbackLocale);
		}

		return new self(
			$defaultLocale,
			$localeWhiteList,
			$fallbackLocales,
			$localeResolver,
			$catalogue,
			$messageFormatter,
			$logger,
		);
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $locale = null): string
	{
		if ($locale !== null) {
			$this->checkValidAndWhitelisted($locale);
		}

		$locale ??= $this->getCurrentLocale();
		$possibleLocales = $this->getPossibleLocales($locale);

		// Should not happen, foreach should always have at least one iteration
		$translatedMessage = null;
		$messageLocale = $locale;

		foreach ($possibleLocales as $messageLocale) {
			$translatedMessage = $this->catalogue->getMessage($message, $messageLocale);

			if ($translatedMessage !== null) {
				break;
			}
		}

		if ($translatedMessage === null) {
			$this->logger->addMissingResource($locale, $message);

			return $message;
		}

		return $this->messageFormatter->formatMessage($messageLocale, $translatedMessage, $parameters);
	}

	public function getDefaultLocale(): string
	{
		return $this->defaultLocale;
	}

	/**
	 * @return array<string>
	 */
	public function getLocaleWhitelist(): array
	{
		return $this->localeWhitelist;
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

		$this->checkValidAndWhitelisted($locale);
		$this->currentLocale = $locale;
	}

	public function getCurrentLocale(): string
	{
		if ($this->currentLocale !== null) {
			return $this->currentLocale;
		}

		$resolved = $this->localeResolver->resolve($this->localeWhitelist);

		if ($resolved !== null) {
			$resolved = LocaleHelper::normalize($resolved);

			if (LocaleHelper::isWhitelisted($resolved, $this->localeWhitelist)) {
				return $this->currentLocale = $resolved;
			}
		}

		return $this->currentLocale = $this->defaultLocale;
	}

	private function checkValidAndWhitelisted(string $locale): void
	{
		LocaleHelper::validate($locale);

		if (!LocaleHelper::isWhitelisted($locale, $this->localeWhitelist)) {
			throw LocaleNotWhitelisted::forWhitelist($locale, $this->localeWhitelist);
		}
	}

	/**
	 * @return array<string>
	 */
	private function getPossibleLocales(string $requestedLocale): array
	{
		if (isset($this->possibleLocales[$requestedLocale])) {
			return $this->possibleLocales[$requestedLocale];
		}

		$list = [];

		// Add requested locale
		$list[] = $requestedLocale;
		if (($shortRequestedLocale = LocaleHelper::shorten($requestedLocale)) !== $requestedLocale) {
			$list[] = $shortRequestedLocale;
		}

		// Add locale from fallback
		if (isset($this->fallbackLocales[$requestedLocale])) {
			$list[] = $fallback = $this->fallbackLocales[$requestedLocale];
			if (($shortFallback = LocaleHelper::shorten($fallback)) !== $fallback) {
				$list[] = $shortFallback;
			}
		}

		// Add short locale from fallback
		if ($requestedLocale !== $shortRequestedLocale && isset($this->fallbackLocales[$shortRequestedLocale])) {
			$list[] = $fallback = $this->fallbackLocales[$shortRequestedLocale];
			if (($shortFallback = LocaleHelper::shorten($fallback)) !== $fallback) {
				$list[] = $shortFallback;
			}
		}

		// Add default locale
		$list[] = $default = $this->defaultLocale;
		if (($shortDefault = LocaleHelper::shorten($default)) !== $default) {
			$list[] = $shortDefault;
		}

		// Remove duplicates
		$list = array_unique($list);

		$this->possibleLocales[$requestedLocale] = $list;

		return $list;
	}

}
