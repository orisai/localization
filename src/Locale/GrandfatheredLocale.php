<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use function strtolower;

final class GrandfatheredLocale implements Locale
{

	/**
	 * Other are lowercase
	 */
	private const GRANDFATHERED_CASE = [
		'en-gb-oed' => 'en-GB-oed',
		'sgn-be-fr' => 'sgn-BE-FR',
		'sgn-be-nl' => 'sgn-BE-NL',
		'sgn-ch-de' => 'sgn-CH-DE',
	];

	private string $tag;

	/** @var array<string> */
	private array $tagVariants = [];

	public function __construct(string $rawTag)
	{
		$tag = strtolower($rawTag);

		$this->tag = self::GRANDFATHERED_CASE[$tag] ?? $tag;
	}

	public function getTag(): string
	{
		return $this->tag;
	}

	/**
	 * @return array<string>
	 */
	public function getTagVariants(): array
	{
		if ($this->tagVariants !== []) {
			return $this->tagVariants;
		}

		$standard = $this->getStandardLocale();

		if ($standard === null) {
			return [$this->getTag()];
		}

		return $this->tagVariants = [...$standard->getTagVariants(), ...[$this->getTag()]];
	}

	public function getLanguage(): string
	{
		return $this->tag;
	}

	/**
	 * @todo - return standard locale
	 */
	public function getStandardLocale(): ?StandardLocale
	{
		return null;
	}

	/**
	 * @return array<mixed>
	 */
	public function __serialize(): array
	{
		return [
			'tag' => $this->getTag(),
			'tagVariants' => $this->getTagVariants(),
		];
	}

	/**
	 * @param array<mixed> $data
	 */
	public function __unserialize(array $data): void
	{
		$this->tag = $data['tag'];
		$this->tagVariants = $data['tagVariants'];
	}

}
