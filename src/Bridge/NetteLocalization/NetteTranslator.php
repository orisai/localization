<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteLocalization;

use Nette\Localization\ITranslator;
use Orisai\Exceptions\Logic\InvalidArgument;
use Orisai\Localization\Translator;
use function is_array;
use function is_float;
use function is_int;
use function is_string;

final class NetteTranslator implements ITranslator
{

	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param mixed $message
	 * @param mixed ...$parameters ['parameters', 'array'], 'locale'
	 */
	public function translate($message, ...$parameters): string
	{
		if (!is_string($message)) {
			return (string) $message;
		}

		$messageParameters = $parameters[0] ?? [];

		if (!is_array($messageParameters)) {
			if ($messageParameters === null) {
				// nette/forms gives null parameter by default
				$messageParameters = [];
			} elseif (is_int($messageParameters) || is_float($messageParameters)) {
				// Count parameter, used in nette/forms
				$messageParameters = ['count' => $messageParameters];
			} else {
				throw InvalidArgument::create()
					->withMessage('Unsupported type of parameter given.');
			}
		}

		$locale = $parameters[1] ?? null;

		return $this->translator->translate($message, $messageParameters, $locale);
	}

}
