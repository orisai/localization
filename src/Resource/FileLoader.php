<?php declare(strict_types = 1);

namespace Orisai\Localization\Resource;

use Orisai\DataSources\DataSource;
use Orisai\Exceptions\Logic\InvalidArgument;
use Webmozart\Glob\Glob;
use Webmozart\PathUtil\Path;
use function array_merge;
use function is_file;

final class FileLoader implements Loader
{

	private DataSource $dataSource;

	/** @var array<string> */
	private array $directories;

	/**
	 * @param array<string> $directories
	 */
	public function __construct(DataSource $dataSource, array $directories)
	{
		$this->dataSource = $dataSource;
		$this->directories = $directories;
	}

	/**
	 * @return array<string, string>
	 */
	public function loadAllMessages(string $languageTag): array
	{
		$translationsByDirectory = [];
		foreach ($this->directories as $directory) {
			$translationsByDirectory[] = $this->loadFromDirectory($directory, $languageTag);
		}

		return array_merge(...$translationsByDirectory);
	}

	/**
	 * @return array<string, string>
	 */
	public function loadFromDirectory(string $directory, string $languageTag): array
	{
		$translationsByPath = [];
		foreach (Glob::glob($directory) as $path) {
			if (!is_file($path)) {
				throw InvalidArgument::create()
					->withMessage("Expected file, {$path} given.");
			}

			if (Path::getFilenameWithoutExtension($path) === $languageTag) {
				$translationsByPath[] = $this->dataSource->fromFile($path);
			}
		}

		return array_merge(...$translationsByPath);
	}

}
