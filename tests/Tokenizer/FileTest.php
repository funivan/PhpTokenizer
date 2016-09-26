<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;

  /**
   *
   */
  class FileTest extends \Test\Funivan\PhpTokenizer\MainTestCase {

    /**
     * @param $code
     * @return \Funivan\PhpTokenizer\File
     */
    protected function getFileObjectWithCode($code) {
      $tempFile = $this->createFileWithCode($code);
      return new \Funivan\PhpTokenizer\File($tempFile);
    }

    public function testStaticOpen() {
      $file = $this->getFileObjectWithCode('<?php 
      echo 1; ');

      static::assertCount(7, $file->getCollection());

      static::assertInternalType('string', $file->getPath());
      $otherFile = \Funivan\PhpTokenizer\File::open($file->getPath());
      static::assertCount(7, $otherFile->getCollection());


      unlink($file->getPath());

    }

    public function testOpen() {
      $file = $this->getFileObjectWithCode('<?php
      echo 1; ');

      static::assertCount(7, $file->getCollection());
      unlink($file->getPath());
    }

    public function testFilePath() {
      $file = $this->getFileObjectWithCode('<?php echo 1; ');
      static::assertNotEmpty($file->getPath());
      unlink($file->getPath());
    }


    public function testSave() {

      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $tokens = $file->find(Query::create()->valueIs('1'));

      static::assertCount(1, $tokens);
      $tokens->each(function (Token $token) {
        $token->setValue(2);
      });

      $file->save();

      $itemsNum = 0;

      $query = new Query();
      $query->valueIs(1);
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $itemsNum++;
        }
      }

      static::assertEquals(0, $itemsNum);

      $itemsNum = 0;
      $query = new Query();
      $query->valueIs(2);
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $itemsNum++;
        }
      }

      static::assertEquals(1, $itemsNum);

      unlink($file->getPath());
    }


    public function testRefresh() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      static::assertCount(5, $file->getCollection());

      $query = new Query();
      $query->valueIs('echo');
      foreach ($file->getCollection() as $token) {
        if ($query->isValid($token)) {
          $token->remove();
        }
      }


      static::assertCount(5, $file->getCollection());
      $file->refresh();

      static::assertCount(4, $file->getCollection());

      $code = $file->getCollection()->assemble();
      static::assertEquals('<?php  1;', $code);

      unlink($file->getPath());
    }

    public function testHtml() {
      # create temp file
      $code = '<html><?php echo 1 ?></html>';

      $file = $this->getFileObjectWithCode($code);

      static::assertCount(8, $file->getCollection());
      unlink($file->getPath());
    }

    public function testSaveFileWithoutChange() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $startModificationTime = \filemtime($file->getPath());

      $file->save();

      $endModificationTime = \filemtime($file->getPath());
      static::assertEquals($endModificationTime, $startModificationTime);
      unlink($file->getPath());
    }


    public function testIsChanged() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      static::assertFalse($file->isChanged());

      $file->getCollection()->getFirst()->setValue('<?php');
      static::assertTrue($file->isChanged());
      unlink($file->getPath());
    }

  }
