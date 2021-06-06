<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

interface LoaderManager
{

	/**
	 * @return array<Loader>
	 */
	public function getAll(): array;

}
