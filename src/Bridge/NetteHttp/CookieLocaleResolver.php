<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteHttp;

use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Orisai\Localization\Exception\MalformedLanguageTag;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\LocaleSet;
use function is_string;

final class CookieLocaleResolver implements LocaleResolver
{

	public const COOKIE_KEY = 'locale';

	private IRequest $request;
	private IResponse $response;

	private LocaleProcessor $processor;

	public function __construct(IRequest $request, IResponse $response, LocaleProcessor $processor)
	{
		$this->request = $request;
		$this->response = $response;
		$this->processor = $processor;
	}

	public function resolve(LocaleSet $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		$languageTag = $this->request->getCookie(self::COOKIE_KEY);

		if ($languageTag === null) {
			return null;
		}

		if (!is_string($languageTag)) {
			$this->response->deleteCookie(self::COOKIE_KEY);

			return null;
		}

		try {
			$this->processor->parse($languageTag);
		} catch (MalformedLanguageTag $error) {
			$this->response->deleteCookie(self::COOKIE_KEY);

			return null;
		}

		return $localeProcessor->parse($languageTag);
	}

}
