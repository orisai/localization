<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Localization\Locale\Locale;
use Orisai\TranslationContracts\Translator as TranslatorContract;

interface Translator extends TranslatorContract
{

	public function getCurrentLocale(): Locale;

	public function getDefaultLocale(): Locale;

	/**
	 * @return array<Locale>
	 */
	public function getAllowedLocales(): array;

}
