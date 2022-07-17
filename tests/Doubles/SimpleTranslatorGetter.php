<?php declare(strict_types = 1);

namespace Tests\Orisai\Localization\Doubles;

use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorGetter;

final class SimpleTranslatorGetter implements TranslatorGetter
{

	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	public function get(): Translator
	{
		return $this->translator;
	}

}
