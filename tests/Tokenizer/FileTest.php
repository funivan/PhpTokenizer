<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Extractor\Extractor;
  use Funivan\PhpTokenizer\Extractor\TokenSequence;
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
      $sequence = new TokenSequence();
      $sequence->strict()->valueIs(1);

      $extractor = new Extractor($file->getCollection(), $sequence);

      $blocks = $extractor->fetchBlocks();


      foreach ($blocks as $blockTokens) {
        $blockTokens->getFirst()->setValue(2);
      }

      $file->save();


      $sequence = new TokenSequence();
      $sequence->strict()->valueIs(1);


      $extractor = new Extractor($file->getCollection(), $sequence);
      $this->assertCount(0, $extractor->fetchBlocks());

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs(2);
      
      $extractor = new Extractor($file->getCollection(), $sequence);
      $this->assertCount(1, $extractor->fetchBlocks());


      unlink($file->getPath());
    }


    public function testRefresh() {
      $file = $this->getFileObjectWithCode('<?php echo 1;');

      $this->assertCount(5, $file->getCollection());

      $sequence = new TokenSequence();
      $sequence->strict()->valueIs('echo');

      $blocks = $sequence->extract($file->getCollection());
      $this->assertCount(1, $blocks->getFirst());

      $blocks->getFirst()->map(function (Token $item) {
        $item->remove();
      });

      $this->assertCount(5, $file->getCollection());
      $file->refresh();

      $this->assertCount(4, $file->getCollection());
      $code = $file->getCollection()->assemble();
      $this->assertEquals('<?php  1;', $code);

      unlink($file->getPath());
    }

    public function testHtml() {
      # create temp file
      $code = '<html><?= 1 ?></html>';


      $file = $file = $this->getFileObjectWithCode($code);

      $this->assertCount(7, $file->getCollection());
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

      $file->getCollection()->getFirst()->setValue('<?');
      $this->assertTrue($file->isChanged());
      unlink($file->getPath());
    }

  }
