<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;

  class FileTest extends \Test\Funivan\PhpTokenizer\Main {

    protected function getFileObjectWithCode($code) {
      $tempFile = $this->createFileWithCode($code);
      return new \Funivan\PhpTokenizer\File($tempFile);
    }

    public function testOpen() {
      $file = $this->getFileObjectWithCode('<?php
      echo 1; ');

      $this->assertCount(7, $file->getCollection());
      unlink($file->getPath());
    }

    public function testFilePath() {
      $file = $this->getFileObjectWithCode('<?php echo 1; ');
      $this->assertNotEmpty($file->getPath());
      unlink($file->getPath());
    }


    public function testSave() {

      $file = $this->getFileObjectWithCode('<?php echo 1;');
      $query = new Query();
      $query->valueIs(1);

      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $token->setValue(2);
        }
      }

      $file->save();

      $itemsNum = 0;

      $query = new Query();
      $query->valueIs(1);
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $itemsNum++;
        }
      }

      $this->assertEquals(0, $itemsNum);
      
      $itemsNum = 0;
      $query = new Query();
      $query->valueIs(2);
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $itemsNum++;
        }
      }

      $this->assertEquals(1, $itemsNum);

      unlink($file->getPath());
    }


    public function testRefresh() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $this->assertCount(5, $file->getCollection());

      $query = new Query();
      $query->valueIs('echo');
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $token->remove();
        }
      }



      $this->assertCount(5, $file->getCollection());
      $file->refresh();

      $this->assertCount(4, $file->getCollection());
      
      $code = $file->getCollection()->assemble();
      $this->assertEquals('<?php  1;', $code);

      unlink($file->getPath());
    }

    public function testHtml() {
      # create temp file
      $code = '<html><?php echo 1 ?></html>';

      $file = $this->getFileObjectWithCode($code);

      $this->assertCount(8, $file->getCollection());
      unlink($file->getPath());
    }

    public function testSaveFileWithoutChange() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $startModificationTime = \filemtime($file->getPath());

      $file->save();

      $endModificationTime = \filemtime($file->getPath());
      $this->assertEquals($endModificationTime, $startModificationTime);
      unlink($file->getPath());
    }


    public function testIsChanged() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $this->assertFalse($file->isChanged());

      $file->getCollection()->getFirst()->setValue('<?php');
      $this->assertTrue($file->isChanged());
      unlink($file->getPath());
    }

  }
