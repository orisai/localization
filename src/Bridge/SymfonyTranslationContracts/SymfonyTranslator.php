<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\SymfonyTranslationContracts;

use Orisai\Localization\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use function sprintf;

final class SymfonyTranslator implements TranslatorInterface
{

	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function trans(
		string $message,
		array $parameters = [],
		?string $domain = null,
		?string $locale = null
	): string
	{
		if ($domain !== null) {
			$message = sprintf('%s.%s', $domain, $message);
		}

		return $this->translator->translate($message, $parameters, $locale);
	}

}
