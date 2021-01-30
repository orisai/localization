<?php declare(strict_types = 1);

namespace Orisai\Localization\Bridge\NetteRouting;

use Nette\Http\IRequest;
use Nette\Routing\Router;
use Orisai\Localization\Locale\Locale;
use Orisai\Localization\Locale\LocaleProcessor;
use Orisai\Localization\Locale\LocaleResolver;
use Orisai\Localization\Locale\Locales;
use function array_key_exists;

final class RouterLocaleResolver implements LocaleResolver
{

	private IRequest $request;
	private Router $router;

	private string $parameterName = 'locale';

	public function __construct(IRequest $request, Router $router)
	{
		$this->request = $request;
		$this->router = $router;
	}

	public function setParameterName(string $parameterName): void
	{
		$this->parameterName = $parameterName;
	}

	public function resolve(Locales $locales, LocaleProcessor $localeProcessor): ?Locale
	{
		$match = $this->router->match($this->request);

		if ($match !== null && array_key_exists($this->parameterName, $match)) {
			return $localeProcessor->parse($match[$this->parameterName]);
		}

		return null;
	}

}
