<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;

final class MalformedLanguageTag extends LogicalException
{

	private string $languageTag;

	private function __construct(string $message, string $locale)
	{
		parent::__construct($message);
		$this->languageTag = $locale;
	}

	public static function forUnknownFormat(string $languageTag): self
	{
		return new self("Invalid language tag '{$languageTag}'.", $languageTag);
	}

	public static function forNonNormalizedFormat(string $languageTag, string $normalizedLanguageTag): self
	{
		return new self(
			"Invalid language tag '{$languageTag}', use '{$normalizedLanguageTag}' format instead.",
			$languageTag,
		);
	}

	public function getLanguageTag(): string
	{
		return $this->languageTag;
	}

}
