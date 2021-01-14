<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleSet;
use function implode;

final class LanguageNotWhitelisted extends LogicalException
{

	private Locale $locale;

	/** @var array<string> */
	private array $whitelist;

	/**
	 * @param array<string> $whitelist
	 */
	private function __construct(string $message, Locale $locale, array $whitelist)
	{
		parent::__construct($message);
		$this->locale = $locale;
		$this->whitelist = $whitelist;
	}

	public static function forLocales(Locale $locale, LocaleSet $locales): self
	{
		$whitelist = [];
		foreach ($locales->getWhitelist() as $whitelisted) {
			$whitelist[] = $whitelisted->getLanguage();
		}

		$inlineWhitelist = implode(', ', $whitelist);

		return new self(
			"Language '{$locale->getLanguage()}' is not whitelisted. Whitelisted are: '{$inlineWhitelist}'",
			$locale,
			$whitelist,
		);
	}

	public function getLocale(): Locale
	{
		return $this->locale;
	}

	/**
	 * @return array<string>
	 */
	public function getWhitelist(): array
	{
		return $this->whitelist;
	}

}
