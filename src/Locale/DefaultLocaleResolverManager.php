<?php declare(strict_types = 1);

namespace Orisai\Localization\Locale;

use Orisai\Exceptions\Logic\InvalidArgument;
use function array_keys;

final class DefaultLocaleResolverManager implements LocaleResolverManager
{

	/** @var array<int|string, LocaleResolver> */
	private array $resolvers;

	/**
	 * @param array<int|string, LocaleResolver> $resolvers
	 */
	public function __construct(array $resolvers)
	{
		$this->resolvers = $resolvers;
	}

	/**
	 * @param int|string $key
	 */
	public function get($key): LocaleResolver
	{
		$resolver = $this->resolvers[$key] ?? null;

		if ($resolver === null) {
			throw InvalidArgument::create()
				->withMessage("No resolver is registered with key $key.");
		}

		return $resolver;
	}

	/**
	 * @return array<int|string>
	 */
	public function getKeys(): array
	{
		return array_keys($this->resolvers);
	}

}
