<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Tracy;

use Orisai\Localization\Logging\MissingResource;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Translator;
use Tracy\IBarPanel;
use function ob_get_clean;
use function ob_start;

final class TranslationPanel implements IBarPanel
{

	private Translator $translator;

	/** @var array<MissingResource> */
	private array $missingResources;

	public function __construct(Translator $translator, TranslationsLogger $logger)
	{
		$this->translator = $translator;
		$this->missingResources = $logger->getMissingResources();
	}

	public function getTab(): string
	{
		ob_start();
		require __DIR__ . '/templates/tab.phtml';

		return ob_get_clean();
	}

	public function getPanel(): string
	{
		$currentLocale = $this->translator->getCurrentLocale();
		$defaultLocale = $this->translator->getDefaultLocale();
		$localeWhitelist = $this->translator->getLocaleWhitelist();
		$missingResources = $this->missingResources;

		ob_start();
		require __DIR__ . '/templates/panel.phtml';

		return ob_get_clean();
	}

}
