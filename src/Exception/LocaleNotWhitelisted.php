<?php declare(strict_types = 1);

namespace Orisai\Localization\Exception;

use Orisai\Exceptions\LogicalException;
use function implode;
use function sprintf;

final class LocaleNotWhitelisted extends LogicalException
{

	private string $locale;

	/** @var array<string> */
	private array $whitelist;

	/**
	 * @param array<string> $whitelist
	 */
	private function __construct(string $message, string $locale, array $whitelist)
	{
		parent::__construct($message);
		$this->locale = $locale;
		$this->whitelist = $whitelist;
	}

	/**
	 * @param array<string> $whitelist
	 */
	public static function forWhitelist(string $locale, array $whitelist): self
	{
		return new self(
			sprintf('Locale "%s" is not whitelisted. Whitelisted are: "%s"', $locale, implode(', ', $whitelist)),
			$locale,
			$whitelist,
		);
	}

	public function getLocale(): string
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
