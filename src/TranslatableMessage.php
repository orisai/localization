<?php declare(strict_types = 1);

namespace Orisai\Localization;

final class TranslatableMessage
{

	private string $message;

	/** @var array<mixed> */
	private array $parameters;

	private ?string $languageTag;

	/**
	 * @param array<mixed> $parameters
	 */
	public function __construct(string $message, array $parameters = [], ?string $languageTag = null)
	{
		$this->message = $message;
		$this->parameters = $parameters;
		$this->languageTag = $languageTag;
	}

	public function getMessage(): string
	{
		return $this->message;
	}

	/**
	 * @return array<mixed>
	 */
	public function getParameters(): array
	{
		return $this->parameters;
	}

	public function getLanguageTag(): ?string
	{
		return $this->languageTag;
	}

}
