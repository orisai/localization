<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleSet;
use function implode;

final class LanguageNotAllowed extends LogicalException
{

	private Locale $locale;

	/** @var array<string> */
	private array $allowed;

	/**
	 * @param array<string> $allowed
	 */
	private function __construct(string $message, Locale $locale, array $allowed)
	{
		parent::__construct($message);
		$this->locale = $locale;
		$this->allowed = $allowed;
	}

	public static function forLocales(Locale $locale, LocaleSet $locales): self
	{
		$allowed = [];
		foreach ($locales->getAllowed() as $allowedLocale) {
			$allowed[] = $allowedLocale->getLanguage();
		}

		$allowedInline = implode(', ', $allowed);

		return new self(
			"Language '{$locale->getLanguage()}' is not allowed. Allowed are: '{$allowedInline}'",
			$locale,
			$allowed,
		);
	}

	public function getLocale(): Locale
	{
		return $this->locale;
	}

	/**
	 * @return array<string>
	 */
	public function getAllowed(): array
	{
		return $this->allowed;
	}

}
