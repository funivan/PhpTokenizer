<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Query;

  class MovesTest extends \Test\Funivan\PhpTokenizer\Main {

    public function _testMoveWithoutQueries() {
      $file = $this->initFileWithCode('<?php
         echo 1+5;
      ');

      $q = $file->getCollection()->extendedQuery();
      $e = null;
      try {
        $q->move(2);
      } catch (\Exception $e) {
      }
      $this->assertInstanceOf('Exception', $e);

      unlink($file->getPath());
    }

    public function _testMoveAfterStrict() {
      $file = $this->initFileWithCode('<?php
         echo 1+5;
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->move(2);

      $blocks = $q->getBlock();
      $this->assertCount(1, $blocks);
      $this->assertCount(3, $blocks->getFirst());
      $this->assertEquals('echo 1', (string) $blocks->getFirst());
      
      unlink($file->getPath());
    }

    public function _testMoveAfterPossible() {
      $file = $this->initFileWithCode('<?php
         echo 1+5;
         echo 1-5;
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->strict()->typeIs(T_WHITESPACE);
      $q->strict()->valueIs('1');
      $q->possible()->valueIs('+');

      $blocks = $q->getBlock();
      $this->assertCount(2, $blocks);
      $this->assertEquals('echo 1+', (string) $blocks->getFirst());
      $this->assertEquals('echo 1', (string) $blocks->getLast());

      $q->move(-1);
      $blocks = $q->getBlock();

      $this->assertEquals('echo 1', (string) $blocks->getFirst());
      $this->assertEquals('echo 1', (string) $blocks->getLast());

      $q->move(+2);
      $blocks = $q->getBlock();

      $this->assertEquals('echo 1+5;', (string) $blocks->getFirst());
      $this->assertEquals('echo 1', (string) $blocks->getLast());

      unlink($file->getPath());
    }

    public function _testMoveAfterSearch() {
      $file = $this->initFileWithCode('<?php
         echo 1+5-4-6+4/5;
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('echo');
      $q->search()->valueIs('/');
      $q->move(2);
      $blocks = $q->getBlock();

      $this->assertEquals('echo 1+5-4-6+4/5;', (string) $blocks->getFirst());

      $q->move(-2);
      $q->move(-3);
      $blocks = $q->getBlock();

      $this->assertEquals('echo 1+5-4-6', (string) $blocks->getFirst());

      unlink($file->getPath());
    }

    public function _testMoveAfterSection() {
      $file = $this->initFileWithCode('<?php
         function ($a){
                          echo 123;
         }
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('function');
      $q->section('(', ')');
      $q->move(-1);
      $blocks = $q->getBlock();

      $this->assertEquals('function ($a', (string) $blocks->getFirst());

      $q->move(1);
      $blocks = $q->getBlock();
      $this->assertEquals('function ($a){', (string) $blocks->getFirst());
      
      unlink($file->getPath());
    }

    public function _testMoveWithSpaces() {
      $file = $this->initFileWithCode('<?php
         function ($a){
         }
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('function');
      $q->strict()->valueIs('(');
      $q->move(2);
      $q->insertWhitespaceQueries();
      $blocks = $q->getBlock();

      $this->assertEquals('function ($a)', (string) $blocks->getFirst());

      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('function');
      $q->strict()->valueIs('(');
      $q->move(-2);

      $q->insertWhitespaceQueries();
      $blocks = $q->getBlock();
      $this->assertEquals('function', (string) $blocks->getFirst());

      unlink($file->getPath());

    }

    public function _testMoveExample() {
      $file = $this->initFileWithCode('<?php
      $user = $this->getUser();
      $dealer = $this->getDealer();
      $user = "test";
      ');
      $q = $file->getCollection()->extendedQuery();
      $q->strict()->valueIs('$user');
      $q->search()->valueIs(';');
      $q->move(-3);
      $blocks = $q->getBlock();

      $this->assertEquals('$user = $this->getUser', (string) $blocks->getFirst());

      unlink($file->getPath());
    }

  }
