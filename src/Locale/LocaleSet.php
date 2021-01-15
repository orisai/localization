<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use Orisai\Exceptions\Logic\InvalidArgument;
use function in_array;

final class LocaleSet
{

	private Locale $default;

	/** @var array<Locale> */
	private array $allowed;

	/** @var array<string, Locale> */
	private array $fallbacks;

	/**
	 * @param array<string>         $allowed
	 * @param array<string, string> $fallbacks
	 */
	public function __construct(LocaleProcessor $localeProcessor, string $default, array $allowed, array $fallbacks)
	{
		$this->default = $localeProcessor->parseAndEnsureNormalized($default);

		$defaultLanguage = $this->default->getLanguage();
		if (!in_array($defaultLanguage, $allowed, true)) {
			$allowed[] = $defaultLanguage;
		}

		$this->setAllowed($allowed, $localeProcessor);
		$this->setFallbacks($fallbacks, $localeProcessor);
	}

	/**
	 * @param array<string> $allowed
	 */
	protected function setAllowed(array $allowed, LocaleProcessor $localeProcessor): void
	{
		$processed = [];
		foreach ($allowed as $languageTag) {
			$locale = $localeProcessor->parseAndEnsureNormalized($languageTag);

			$language = $locale->getLanguage();
			$tag = $locale->getTag();

			if ($language !== $tag) {
				throw InvalidArgument::create()
					->withMessage(
						"Only language can be allowed, use {$language} instead of {$tag}",
					);
			}

			$processed[$language] = $locale;
		}

		$this->allowed = $processed;
	}

	/**
	 * @param array<string, string> $fallbacks
	 */
	protected function setFallbacks(array $fallbacks, LocaleProcessor $localeProcessor): void
	{
		$processed = [];
		foreach ($fallbacks as $requested => $fallback) {
			$requestedProcessed = $localeProcessor->parseAndEnsureNormalized($requested);
			$fallbackProcessed = $localeProcessor->parseAndEnsureNormalized($fallback);

			$expectedLanguage = $requestedProcessed->getLanguage();
			$requestedTag = $requestedProcessed->getTag();

			if ($expectedLanguage !== $requestedTag) {
				throw InvalidArgument::create()
					->withMessage(
						"Fallback can be configured only for language, use {$expectedLanguage} instead of {$requestedTag}",
					);
			}

			if ($expectedLanguage === $fallbackProcessed->getLanguage()) {
				throw InvalidArgument::create()
					->withMessage(
						"Language tag {$fallbackProcessed->getTag()} cannot be used as a {$expectedLanguage} language fallback, it's the same language.",
					);
			}

			$processed[$expectedLanguage] = $fallbackProcessed;
		}

		$this->fallbacks = $processed;
	}

	public function getDefault(): Locale
	{
		return $this->default;
	}

	/**
	 * @return array<Locale>
	 */
	public function getAllowed(): array
	{
		return $this->allowed;
	}

	/**
	 * @return array<Locale>
	 */
	public function getFallbacks(): array
	{
		return $this->fallbacks;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'default' => $this->default,
			'allowed' => $this->allowed,
			'fallbacks' => $this->fallbacks,
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->default = $data['default'];
		$this->allowed = $data['allowed'];
		$this->fallbacks = $data['fallbacks'];
	}

}
