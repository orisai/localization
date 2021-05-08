<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Tracy;

use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Logging\TranslationsLogger;
use Orisai\Localization\Translator;
use Tracy\Helpers;
use Tracy\IBarPanel;
use function array_map;

final class TranslationPanel implements IBarPanel
{

	private Translator $translator;

	private TranslationsLogger $logger;

	public function __construct(Translator $translator, TranslationsLogger $logger)
	{
		$this->translator = $translator;
		$this->logger = $logger;
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
			$allowedLocales = array_map(
				static fn (Locale $locale): string => $locale->getLanguage(),
				$this->translator->getAllowedLocales(),
			);
			$missingResources = $this->logger->getMissingResources();

			require __DIR__ . '/templates/panel.phtml';
		});
	}

}
