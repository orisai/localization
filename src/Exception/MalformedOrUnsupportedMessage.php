<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use function sprintf;

final class MalformedOrUnsupportedMessage extends LogicalException
{

	private string $pattern;

	private string $languageTag;

	private function __construct(string $message, string $pattern, string $languageTag)
	{
		parent::__construct($message);
		$this->pattern = $pattern;
		$this->languageTag = $languageTag;
	}

	public static function forPattern(string $pattern, string $languageTag): self
	{
		return new self(
			sprintf(
				'Message pattern "%s" is invalid or not supported.',
				$pattern,
			),
			$pattern,
			$languageTag,
		);
	}

	public function getPattern(): string
	{
		return $this->pattern;
	}

	public function getLanguageTag(): string
	{
		return $this->languageTag;
	}

}
