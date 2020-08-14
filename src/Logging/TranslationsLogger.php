<?php declare(strict_types = 1);

namespace Orisai\Localization\Logging;

use Psr\Log\LoggerInterface;
use function md5;
use function sprintf;

/**
 * @internal
 */
final class TranslationsLogger
{

	private ?LoggerInterface $logger = null;

	/** @var array<MissingResource> */
	private array $missingResources = [];

	public function __construct(?LoggerInterface $logger = null)
	{
		$this->logger = $logger;
	}

	public function addMissingResource(string $locale, string $message): void
	{
		$hash = md5($message);

		if (isset($this->missingResources[$hash])) {
			$this->missingResources[$hash]->incrementCount($locale);
		} else {
			$this->missingResources[$hash] = new MissingResource($locale, $message);
		}

		if ($this->logger === null) {
			return;
		}

		$this->logger->error(
			sprintf('Missing translation of "%s" for locale "%s"', $message, $locale),
			[
				'locale' => $locale,
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
