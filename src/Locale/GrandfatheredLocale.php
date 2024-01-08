<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use function strtolower;

final class GrandfatheredLocale implements Locale
{

	/**
	 * Other are lowercase
	 */
	private const GrandfatheredCase = [
		'en-gb-oed' => 'en-GB-oed',
		'sgn-be-fr' => 'sgn-BE-FR',
		'sgn-be-nl' => 'sgn-BE-NL',
		'sgn-ch-de' => 'sgn-CH-DE',
	];

	private string $tag;

	/** @var array<int, string> */
	private array $tagVariants = [];

	public function __construct(string $rawTag)
	{
		$tag = strtolower($rawTag);

		$this->tag = self::GrandfatheredCase[$tag] ?? $tag;
	}

	public function getTag(): string
	{
		return $this->tag;
	}

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

	public function __serialize(): array
	{
		return [
			'tag' => $this->getTag(),
			'tagVariants' => $this->getTagVariants(),
		];
	}

	public function __unserialize(array $data): void
	{
		$this->tag = $data['tag'];
		$this->tagVariants = $data['tagVariants'];
	}

}
