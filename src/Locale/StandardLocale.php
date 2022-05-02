<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use function str_contains;
use function strrpos;
use function strtolower;
use function strtoupper;
use function substr;
use function ucfirst;

final class StandardLocale implements Locale
{

	private string $primaryLanguage;

	private ?string $extendedLanguage;

	private ?string $script;

	private ?string $region;

	private ?string $variants;

	private ?string $extensions;

	private ?string $private;

	private ?string $language = null;

	private ?string $tag = null;

	/** @var array<string> */
	private array $tagVariants = [];

	public function __construct(
		string $primaryLanguage,
		?string $extendedLanguage,
		?string $script,
		?string $region,
		?string $variants,
		?string $extensions,
		?string $private
	)
	{
		$this->primaryLanguage = $primaryLanguage;
		$this->extendedLanguage = $extendedLanguage;
		$this->script = $script;
		$this->region = $region;
		$this->variants = $variants;
		$this->extensions = $extensions;
		$this->private = $private;
	}

	public function getTag(): string
	{
		return $this->tag ??
			($this->tag = $this->getLanguage() .
				($this->script !== null ? '-' . ucfirst(strtolower($this->script)) : '') .
				($this->region !== null ? '-' . strtoupper($this->region) : '') .
				($this->variants !== null ? '-' . strtolower($this->variants) : '') .
				($this->extensions !== null ? '-' . strtolower($this->extensions) : '') .
				($this->private !== null ? '-' . strtolower($this->private) : '')
			);
	}

	public function getTagVariants(): array
	{
		if ($this->tagVariants !== []) {
			return $this->tagVariants;
		}

		$fullTag = $currentTag = $this->getTag();
		$tags = [$fullTag];

		while (str_contains($currentTag, '-')) {
			$currentTag = substr($currentTag, 0, strrpos($currentTag, '-'));
			$tags[] = $currentTag;
		}

		return $this->tagVariants = $tags;
	}

	public function getLanguage(): string
	{
		return $this->language ??
			($this->language = strtolower($this->primaryLanguage) .
				($this->extendedLanguage !== null ? '-' . strtolower($this->extendedLanguage) : '')
			);
	}

	public function __serialize(): array
	{
		return [
			'primaryLanguage' => $this->primaryLanguage,
			'extendedLanguage' => $this->extendedLanguage,
			'script' => $this->script,
			'region' => $this->region,
			'variants' => $this->variants,
			'extensions' => $this->extensions,
			'private' => $this->private,

			'tag' => $this->getTag(),
			'tagVariants' => $this->getTagVariants(),
			'language' => $this->getLanguage(),
		];
	}

	public function __unserialize(array $data): void
	{
		$this->primaryLanguage = $data['primaryLanguage'];
		$this->extendedLanguage = $data['extendedLanguage'];
		$this->script = $data['script'];
		$this->region = $data['region'];
		$this->variants = $data['variants'];
		$this->extensions = $data['extensions'];
		$this->private = $data['private'];

		$this->tag = $data['tag'];
		$this->tagVariants = $data['tagVariants'];
		$this->language = $data['language'];
	}

}
