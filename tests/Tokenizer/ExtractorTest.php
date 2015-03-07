<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/7/15
   */
  class ExtractorTest extends \PHPUnit_Framework_TestCase {

    public function _testExtractor(){

      $extractor = ClassExtractor::create()->find(
        ClassMethodDeclaration::create()
         ->nameLike('^action.*$')
         ->find(
          TokenSquence::create()
            ->strict()->valueIs('header')
            ->strict()->valueIs('(')
            ->strict()->valueLike('!^\s*location\s*:!i')
            ->search()->valueIs(';')
         )
      );

      $blocks = $extractor->extract($collection, 'function');
      $blocks;


      $extractor = ClassExtractor::create()->find(
        MethodExtractor::create()
          ->nameLike('^action.*$')
          ->findTarget(
            FunctionCall::create()
              ->nameIs('header')
              ->argument(0, '!^["\']?\s*location\s*:!i')
          )
      );

      $blocks = $extractor->extract($collection);
      $blocks;

      $extractor = TokenSequence::create()
        ->strict()->valueIs('class')
        ->strict()->valueLike('.*')
        ->search()->valueIs("{");


      $extractor = ClassExtractor::create()->find(
        ClassConstantDeclaration::create()
      );
      $extractor = ClassExtractor::create()->find(
        ClassPropertyDeclaration::create()
      );
      $extractor = ClassExtractor::create()->find(
        ClassMethodDeclaration::create()
      );

      $extractor = TokenSequence::create()
        ->strict()->typeIs(T_WHITESPACE)
        ->strict()->valueIs('return');


      ClassMethodDeclaration::create()
        ->find(
        TokenSequence::create()
          ->strict()->valueIs('if')
          ->section('(',')')->filter(
            FunctionCall::create()
              ->name('empty')
              ->argument(0, '^$_(GET|POST)')

          )

        );

    }

  }
