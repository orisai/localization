<?php declare(strict_types = 1);

namespace Orisai\Localization;

use Orisai\Exceptions\Logic\InvalidState;
use function sprintf;

final class TranslatorHolder
{

	private static ?TranslatorGetter $translatorGetter = null;

	public static function setTranslatorGetter(TranslatorGetter $translatorGetter): void
	{
		self::$translatorGetter = $translatorGetter;
	}

	public static function getTranslator(): Translator
	{
		if (self::$translatorGetter === null) {
			throw InvalidState::create()
				->withMessage(sprintf(
					'Call %s::setTranslatorGetter() to use %s(), %s\t() and %s\tm().',
					self::class,
					__METHOD__,
					__NAMESPACE__,
					__NAMESPACE__,
				));
		}

		return self::$translatorGetter->get();
	}

}
