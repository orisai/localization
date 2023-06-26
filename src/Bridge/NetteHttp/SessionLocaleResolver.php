<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use Nette\Http\IResponse;
use Nette\Http\Session;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\Locales;
use function sprintf;
use function trigger_error;
use const E_USER_WARNING;

final class SessionLocaleResolver implements LocaleResolver
{

	public const Section = 'orisai.localization';

	public const Parameter = 'locale';

	private IResponse $response;

	private Session $session;

	public function __construct(Session $session, IResponse $response)
	{
		$this->response = $response;
		$this->session = $session;
	}

	public function resolve(Locales $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		if (!$this->session->exists()) {
			return null;
		}

		if (!$this->session->isStarted() && $this->response->isSent()) {
			trigger_error(
				sprintf(
					'Session has not been started and headers had been already sent. Either start your session earlier or disabled the "%s".',
					self::class,
				),
				E_USER_WARNING,
			);

			return null;
		}

		$hasSection = $this->session->hasSection(self::Section);
		if ($hasSection && isset(($section = $this->session->getSection(self::Section))[self::Parameter])) {
			return $localeProcessor->parse($section[self::Parameter]);
		}

		return null;
	}

}
