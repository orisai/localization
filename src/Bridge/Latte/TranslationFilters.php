<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Latte;

use Latte\Runtime\FilterInfo;
use Orisai\Localization\Translator;

final class TranslationFilters
{

	private Translator $translator;

	public function __construct(Translator $translator)
	{
		$this->translator = $translator;
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(
		FilterInfo $filterInfo,
		string $message,
		array $parameters = [],
		?string $languageTag = null
	): string
	{
		return $this->translator->translate($message, $parameters, $languageTag);
	}

}
