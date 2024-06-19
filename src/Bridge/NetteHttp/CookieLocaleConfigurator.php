<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use DateTimeInterface;
use Nette\Http\IResponse;
use Nette\Utils\DateTime;
use Orisai\Localization\Locale\LocaleConfigurator;

final class CookieLocaleConfigurator implements LocaleConfigurator
{

	private IResponse $response;

	/** @var string|int|DateTimeInterface|null */
	private $expiration = null;

	public function __construct(IResponse $response)
	{
		$this->response = $response;
	}

	/**
	 * @param string|int|DateTimeInterface|null $expiration
	 */
	public function setCookieExpiration($expiration): void
	{
		$this->expiration = $expiration;
	}

	public function configure(string $languageTag): void
	{
		$expiration = $this->expiration;
		if ($expiration !== null) {
			$expiration = (int) DateTime::from($expiration)->format('U');
		}

		$this->response->setCookie(CookieLocaleResolver::CookieKey, $languageTag, $expiration);
	}

}
