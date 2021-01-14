<?php declare(strict_types = 1);

namespace Orisai\Localization\Logging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use function md5;

/**
 * @internal
 */
final class TranslationsLogger
{

	private LoggerInterface $logger;

	/** @var array<MissingResource> */
	private array $missingResources = [];

	public function __construct(?LoggerInterface $logger = null)
	{
		$this->logger = $logger ?? new NullLogger();
	}

	public function addMissingResource(string $message, string $languageTag): void
	{
		$hash = md5($message);

		if (isset($this->missingResources[$hash])) {
			$this->missingResources[$hash]->incrementCount($languageTag);
		} else {
			$this->missingResources[$hash] = new MissingResource($message, $languageTag);
		}

		$this->logger->error(
			"Missing translation of {$message} for locale {$languageTag}",
			[
				'locale' => $languageTag,
				'message' => $message,
			],
		);
	}

	/**
	 * @return array<MissingResource>
	 */
	public function getMissingResources(): array
	{
		return $this->missingResources;
	}

}
