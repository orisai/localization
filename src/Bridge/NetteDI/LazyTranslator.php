<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteDI;

use Nette\DI\Container;
use Orisai\Localization\Translator;
use Orisai\Localization\TranslatorHolder;
use function assert;

/**
 * @internal
 * @see TranslatorHolder
 */
final class LazyTranslator implements Translator
{

	private Container $container;

	private string $translatorServiceName;

	private ?Translator $translator = null;

	public function __construct(Container $container, string $translatorServiceName)
	{
		$this->container = $container;
		$this->translatorServiceName = $translatorServiceName;
	}

	/**
	 * @param array<mixed> $parameters
	 */
	public function translate(string $message, array $parameters = [], ?string $locale = null): string
	{
		return $this->getTranslator()->translate($message, $parameters, $locale);
	}

	public function getCurrentLocale(): string
	{
		return $this->getTranslator()->getCurrentLocale();
	}

	public function getDefaultLocale(): string
	{
		return $this->getTranslator()->getDefaultLocale();
	}

	/**
	 * @return array<string>
	 */
	public function getLocaleWhitelist(): array
	{
		return $this->getTranslator()->getLocaleWhitelist();
	}

	private function getTranslator(): Translator
	{
		if ($this->translator === null) {
			$translator = $this->container->getService($this->translatorServiceName);
			assert($translator instanceof Translator);
			$this->translator = $translator;
		}

		return $this->translator;
	}

}
