<?php

namespace Kinocomplete\FeedLoader;

use JsonMachine\JsonMachine;
use Webmozart\PathUtil\Path;
use Kinocomplete\Feed\Feed;

class FileParser
{
  /**
   * @var string
   */
  protected $workingDir;

  /**
   * FileParser constructor.
   *
   * @param string $workingDir
   */
  public function __construct($workingDir)
  {
    $this->workingDir = $workingDir;
  }

  /**
   * Parse feed from file.
   *
   * @param Feed $feed
   * @param callable|null $onParse
   * @param callable|null $onProgress
   * @throws \Exception
   */
  public function parse(
    Feed $feed,
    callable $onParse = null,
    callable $onProgress = null
  ) {
    $filePath = Path::join(
      $this->workingDir,
      $feed->getFileName()
    );

    if (!file_exists($filePath))
      throw new \Exception(
        'Файл фида не найден.'
      );

    $feedStream = JsonMachine::fromFile(
      $filePath,
      $feed->getJsonPointer()
    );

    $fileSize = filesize($filePath);
    $processedBytes = 0;

    foreach ($feedStream as $array) {

      if ($onParse)
        $onParse($array);

      $processedBytes += mb_strlen(
        json_encode($array, JSON_UNESCAPED_UNICODE),
        '8bit'
      );

      if ($onProgress)
        $onProgress(
          $fileSize,
          $processedBytes,
          $feed
        );
    }
  }
}