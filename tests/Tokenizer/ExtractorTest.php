<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/7/15
   */
  class ExtractorTest extends \PHPUnit_Framework_TestCase {

    public function _testExtractor() {

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
            ->section('(', ')')->filter(
              FunctionCall::create()
                ->name('empty')
                ->argument(0, '^$_(GET|POST)')
            )

        );

      // Atl_Response::redirectReferer('/');
      // $this->::redirectReferer('/')
      // find only in specific classes 
      $extractor = ClassExtractor::create()
        ->extendClass('AdminController')
        ->find(
          TokenSequence::create()
            ->strict()->valueIs('Atl_Response')
            ->strict()->valueIs('::')
            ->strict()->valueIs('redirectReferer')

        );

      $extractor->find()->map(function (\Funivan\PhpTokenizer\Collection $collection) {
        $collection->removeWhitespaces();
        $collection->refresh();

        $collection[0]->setValue('$this->');
        $collection[1]->remove();
      });

      // $this->response()->redirect('/orders/order_list');
      // $this->redirect('/orders/order_list');

    }


    public function _testMethodCallFinder() {

      # 1. simple method call extractor
      $extractor = MethodCall::create()
        ->objectVariable('$this')
        ->method('response')
        ->method('redirect');

      // $this->response()->redirect();
      // $this->response([1,2,3])->redirect([0], 123);

      # 2. static method on object  
      $extractor = MethodCall::create()
        ->objectVariable('$this')
        ->staticMethod('response');

      // $this::redirect();

      # 2.1. Static method 
      $extractor = MethodCall::create()
        ->className('UserName')->staticMethod('response');

      // UserName::redirect();

      # 3. With method filter
      $extractor = MethodCall::create()
        ->objectVariable('$this')
        ->method(
          MethodCall::create()
            ->name('response')
            ->withArgument(0, '!$.*!')
            ->withArgumentsNum(3)
        );
      
      
      
      

    }

  }
