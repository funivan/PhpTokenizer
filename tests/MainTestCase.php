<?php

  namespace Test\Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\File;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class MainTestCase extends \PHPUnit_Framework_TestCase {


    /**
     * @param $string
     * @return string
     */
    protected function createFileWithCode($string) {
      $path = tempnam('/tmp', 'testFileOther');
      file_put_contents($path, $string);

      return $path;
    }


    /**
     * @param $string
     * @return File
     */
    protected function initFileWithCode($string) {
      $tempFilePath = $this->createFileWithCode($string);
      return new File($tempFilePath);
    }

  }