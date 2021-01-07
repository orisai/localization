<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use Nette\Http\IResponse;
use Nette\Http\Session;
use Orisai\Localization\Locale\LocaleResolver;
use function sprintf;
use function trigger_error;
use const E_USER_WARNING;

final class SessionLocaleResolver implements LocaleResolver
{

	public const SECTION = 'orisai.localization';
	public const PARAMETER = 'locale';

	private IResponse $response;

	private Session $session;

	public function __construct(Session $session, IResponse $response)
	{
		$this->response = $response;
		$this->session = $session;
	}

	/**
	 * @param array<string> $localeWhitelist
	 */
	public function resolve(array $localeWhitelist): ?string
	{
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

		$hasSection = $this->session->hasSection(self::SECTION);
		if ($hasSection && ($section = $this->session->getSection(self::SECTION))->offsetExists(self::PARAMETER)) {
			return $section->offsetGet(self::PARAMETER);
		}

		return null;
	}

}
