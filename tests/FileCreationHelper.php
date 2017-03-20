<?php

  declare(strict_types=1);


  namespace Test\Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\File;

  class FileCreationHelper {

    /**
     * @param string $code
     * @return File
     */
    public static function createFileFromCode(string $code) : File {
      $path = tempnam('/tmp', 'testFileOther');
      file_put_contents($path, $code);

      return new File($path);
    }
  }