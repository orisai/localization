<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use DateTimeInterface;
use Nette\Http\IResponse;
use Orisai\Localization\Locale\LocaleConfigurator;

final class CookieLocaleConfigurator implements LocaleConfigurator
{

	private IResponse $response;

	/** @var string|int|DateTimeInterface */
	private $expiration = '1 year';

	public function __construct(IResponse $response)
	{
		$this->response = $response;
	}

	/**
	 * @param string|int|DateTimeInterface $expiration
	 */
	public function setCookieExpiration($expiration): void
	{
		$this->expiration = $expiration;
	}

	public function configure(string $languageTag): void
	{
		$this->response->setCookie(CookieLocaleResolver::CookieKey, $languageTag, $this->expiration);
	}

}
