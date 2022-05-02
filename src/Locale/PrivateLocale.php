<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

final class PrivateLocale implements Locale
{

	private string $language;

	public function __construct(string $language)
	{
		$this->language = $language;
	}

	public function getTag(): string
	{
		return $this->getLanguage();
	}

	public function getTagVariants(): array
	{
		return [$this->getTag()];
	}

	public function getLanguage(): string
	{
		return $this->language;
	}

	public function __serialize(): array
	{
		return [
			'language' => $this->language,
		];
	}

	public function __unserialize(array $data): void
	{
		$this->language = $data['language'];
	}

}
