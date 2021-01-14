<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

interface Loader
{

	/**
	 * @return array<string>
	 */
	public function loadAllMessages(string $languageTag): array;

}
