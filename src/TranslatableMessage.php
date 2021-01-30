<?php declare(strict_types = 1);

namespace Orisai\Localization;

final class TranslatableMessage
{

	private string $message;

	/** @var array<mixed> */
	private array $parameters;

	/**
	 * @param array<mixed> $parameters
	 */
	public function __construct(string $message, array $parameters = [])
	{
		$this->message = $message;
		$this->parameters = $parameters;
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

	public function translate(Translator $translator, ?string $languageTag = null): string
	{
		return $translator->translate($this->message, $this->parameters, $languageTag);
	}

}
