<?php

  namespace Test\Funivan\PhpTokenizer\Custom;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 12/18/13
   */
  class ModificationTest extends \Test\Funivan\PhpTokenizer\Main {

    /**
     *
     */
    public function testModifyStringsConcatenation() {

      $filePath = $this->getDemoDataDir() . '/strings.php';

      $file = new \Funivan\PhpTokenizer\File($filePath);
      new \Test\Funivan\PhpTokenizer\Demo\ConcatenationOptimize($file);
      $code = (string) $file->getCollection();

      $newStrings = array(
        '"`".$f."`";',
        '$a;',
        '"   test".$test." + ".$a."\"";',
        '"object ".$this->table."+other"',
        '$this->table."+new"',
      );
      foreach ($newStrings as $string) {
        $this->assertContains($string, $code);
      }

    }

    public function _testStringsInside() {

      $file = new \Funivan\PhpTokenizer\File($this->getDemoDataDir() . '/stringsInside.php');

      new \Demo\ConcatenationOptimize($file);

      $code = $file->getCollection()->assemble();
      $newStrings = [
        '"test \'".$_GET["d"]."\' new";',
      ];
      foreach ($newStrings as $string) {
        $this->assertContains($string, $code);
      }

    }
  }
