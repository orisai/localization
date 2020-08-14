<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use function sprintf;

final class MalformedOrUnsupportedMessage extends LogicalException
{

	private string $pattern;

	private string $locale;

	private function __construct(string $message, string $pattern, string $locale)
	{
		parent::__construct($message);
		$this->pattern = $pattern;
		$this->locale = $locale;
	}

	public static function forPattern(string $pattern, string $locale): self
	{
		return new self(
			sprintf(
				'Message pattern "%s" is invalid or not supported.',
				$pattern,
			),
			$pattern,
			$locale,
		);
	}

	public function getPattern(): string
	{
		return $this->pattern;
	}

	public function getLocale(): string
	{
		return $this->locale;
	}

}
