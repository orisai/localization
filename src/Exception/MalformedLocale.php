<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use function sprintf;

final class MalformedLocale extends LogicalException
{

	private string $locale;

	private function __construct(string $message, string $locale)
	{
		parent::__construct($message);
		$this->locale = $locale;
	}

	public static function forUnknownFormat(string $locale): self
	{
		return new self(
			sprintf('Invalid "%s" locale.', $locale),
			$locale,
		);
	}

	public static function forNonNormalizedFormat(string $locale, string $normalized): self
	{
		return new self(
			sprintf('Invalid "%s" locale, use "%s" format instead.', $locale, $normalized),
			$locale,
		);
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

}
