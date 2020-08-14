<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\Nette\Http;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Orisai\Localization\Exception\MalformedLocale;
use Orisai\Localization\Locale\LocaleHelper;
use Orisai\Localization\Locale\LocaleResolver;
use function is_string;

final class CookieLocaleResolver implements LocaleResolver
{

	public const COOKIE_KEY = 'locale';

	private IRequest $request;

	private IResponse $response;

	public function __construct(IRequest $request, IResponse $response)
	{
		$this->request = $request;
		$this->response = $response;
	}

	/**
	 * @param array<string> $localeWhitelist
	 */
	public function resolve(array $localeWhitelist): ?string
	{
		$locale = $this->request->getCookie(self::COOKIE_KEY);

		if ($locale === null) {
			return null;
		}

		if (!is_string($locale)) {
			$this->response->deleteCookie(self::COOKIE_KEY);

			return null;
		}

		try {
			LocaleHelper::validate($locale);
		} catch (MalformedLocale $error) {
			$this->response->deleteCookie(self::COOKIE_KEY);

			return null;
		}

		return $locale;
	}

}
