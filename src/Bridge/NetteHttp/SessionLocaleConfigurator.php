<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use Nette\Http\Session;
use Orisai\Localization\Locale\LocaleConfigurator;

final class SessionLocaleConfigurator implements LocaleConfigurator
{

	private Session $session;

	public function __construct(Session $session)
	{
		$this->session = $session;
	}

	public function configure(string $languageTag): void
	{
		$this->session->getSection(SessionLocaleResolver::Section)[SessionLocaleResolver::Parameter] = $languageTag;
	}

}
