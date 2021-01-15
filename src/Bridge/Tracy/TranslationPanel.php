<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Tracy;

use Orisai\Localization\Logging\MissingResource;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Translator;
use Tracy\Helpers;
use Tracy\IBarPanel;

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
		return Helpers::capture(static function (): void {
			require __DIR__ . '/templates/tab.phtml';
		});
	}

	public function getPanel(): string
	{
		return Helpers::capture(function (): void {
			$currentLocale = $this->translator->getCurrentLocale();
			$defaultLocale = $this->translator->getDefaultLocale();
			$localeWhitelist = $this->translator->getLocaleWhitelist();
			$missingResources = $this->missingResources;

			require __DIR__ . '/templates/panel.phtml';
		});
	}

}
