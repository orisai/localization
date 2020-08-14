<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

final class TranslatorHolder
{

	private static ?Translator $translator = null;

	/** @var static|null */
	private static $instSelf;

	private Translator $instTranslator;

	private function __construct(Translator $translator)
	{
		$this->instTranslator = $translator;
	}

	public static function getInstance(): self
	{
		if (self::$instSelf === null) {
			if (self::$translator === null) {
				throw InvalidState::create()
					->withMessage(sprintf('Call %s::setTranslator() to use %s()', self::class, __METHOD__));
			}

			self::$instSelf = new self(self::$translator);
		}

		return self::$instSelf;
	}

	public static function setTranslator(Translator $translator): void
	{
		self::$translator = $translator;
	}

	public function getTranslator(): Translator
	{
		return $this->instTranslator;
	}

}
