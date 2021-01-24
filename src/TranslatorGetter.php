<?php declare(strict_types = 1);

namespace Orisai\Localization;

interface TranslatorGetter
{

	public function get(): Translator;

}
