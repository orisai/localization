<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteNeon;

use Orisai\Localization\Resource\Loader;

class NeonLoader implements Loader
{

	/**
	 * @return array<string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		// todo - načíst překlady
		//		- klíče musí být string
		//		- překlady musí odpovídat patternu pro daný jazyk MessageFormatter:validatePattern
		return [];
	}

}
