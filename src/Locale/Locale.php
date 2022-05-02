<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

interface Locale
{

	public function getTag(): string;

	/**
	 * @return array<int, string>
	 */
	public function getTagVariants(): array;

	/**
	 * Primary and extended language (or similar in case of non-standard locales)
	 */
	public function getLanguage(): string;

	/**
	 * @return array<int|string, mixed>
	 */
	public function __serialize(): array;

	/**
	 * @param array<int|string, mixed> $data
	 */
	public function __unserialize(array $data): void;

}
