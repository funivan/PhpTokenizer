<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Token;

  class FileTest extends \Test\Funivan\PhpTokenizer\Main {


    public function testOpen() {
      $file = new \Funivan\PhpTokenizer\File($this->getDemoDataDir() . '/demo.php');
      $this->assertCount(7, $file->getCollection());
    }

    public function testFilePath() {
      $file = \Funivan\PhpTokenizer\File::open($this->getDemoDataDir() . '/demo.php');
      $this->assertContains('files/demo.php', $file->getPath());
    }


    public function testSave() {
      $data = '<?php echo 1;';

      # create temp file
      $tempFile = $this->createFileWithCode($data);

      $file = new \Funivan\PhpTokenizer\File($tempFile);
      $q = $file->getCollection()->query();
      $q->valueIs(1);

      foreach ($q->getTokens() as $token) {
        $token->setValue(2);
      }

      $file->save();

      $file = new \Funivan\PhpTokenizer\File($tempFile);
      $q = $file->getCollection()->query();
      $q->valueIs(2);

      $this->assertCount(1, $q->getTokens());

      unlink($tempFile);
    }


    public function testRefresh() {
      $file = new \Funivan\PhpTokenizer\File($this->getDemoDataDir() . '/demo.php');
      $q = $file->getCollection()->query();

      $this->assertCount(7, $q->getTokens());

      $q->valueIs('echo');

      $tokens = $q->getTokens();

      $this->assertCount(1, $tokens);

      $tokens->map(function (Token $item) {
        $item->remove();
      });

      $this->assertCount(7, $file->getCollection());

      $file->refresh();

      $this->assertCount(5, $file->getCollection());

    }

    public function testHtml() {
      # create temp file
      $code = '<html><?= 1 ?></html>';

      $tempFile = $this->createFileWithCode($code);
      $file = new \Funivan\PhpTokenizer\File($tempFile);

      $this->assertCount(7, $file->getCollection());
    }

    public function testSaveFileWithoutChange() {
      $file = new \Funivan\PhpTokenizer\File($this->getDemoDataDir() . '/demo.php');
      $startModificationTime = \filemtime($file->getPath());
      sleep(1);
      $file->save();

      $endModificationTime = \filemtime($file->getPath());
      $this->assertEquals($endModificationTime, $startModificationTime);
    }

  }
