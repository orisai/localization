<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

interface Catalogue
{

	public function getMessage(string $message, string $languageTag): ?string;

}
