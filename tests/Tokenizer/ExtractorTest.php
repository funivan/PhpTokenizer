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

      # simple method call
      FuctionCall::create()
        ->name('header');
      // header()       -> fire
      // header($a,$b)  -> fire

      # without arguments
      FuctionCall::create()
        ->name('header')
        ->withoutArguments();
      // header()        -> fire
      // header($header) -> skip

      # with custom argument filter
      FuctionCall::create()
        ->name('header')
        ->argument(0, \ArrayExtractor::create());
      // header(array())          -> fire
      // header(array(), 123)     -> fire
      // header()                 -> skip
      // header('')               -> skip
      // header(new stdClass())   -> skip

      MethodCall::create()
        ->reference('$this')
        ->method('getUserName');

      // $this->getUserName()         -> fire
      // $this->getUserName(123)      -> fire
      // CustomObject::getUserName()  -> skip

      MethodCall::create()
        ->reference('$this')
        ->method('getStorage')
        ->method('getUserName');

      // $this->getStorage()->getUserName() -> fire

      MethodCall::create()
        ->reference('$user')
        ->staticMethod('getStorage');
      // $user::getStorage()  -> fire

      MethodCall::create()
        ->reference('$user')
        ->staticMethod('getStorage')
        ->method('getUser');
      // $user::getStorage()->getUser()  -> fire

      MethodCall::create()
        ->reference('StorageComponent')
        ->staticMethod('getStorage')
        ->method('getUser');
      // StorageComponent::getStorage()->getUser()  -> fire

      # 1. simple method call extractor
      MethodCall::create()
        ->reference('$this')
        ->method('response')
        ->method('redirect');

      // $this->response()->redirect();
      // $this->response([1,2,3])->redirect([0], 123);

      # static method on object  
      MethodCall::create()
        ->reference('$this')
        ->staticMethod('response');

      // $this::redirect();

      # static method 
      MethodCall::create()
        ->className('UserName')->staticMethod('response');

      // UserName::redirect();

      # With method filter
      MethodCall::create()
        ->reference('$this')
        ->method(
          MethodCall::create()
            ->name('response')
            ->withArgument(0, '!$.*!')
            ->withArgumentsNum(3)
        );

      # method and property
      # //@todo create name for method and property call  
      StmCall::create()
        ->reference('$this')
        ->staticMethod('response')
        ->property('user')
        ->property('45');

      $this->getReference()->user;

    }

    public function _testProperty() {

      Property::create()
        ->reference('$this')
        ->property('userName');
      
      // $this->userName    -> fire
      // $this->name        -> skip
      
      Property::create()
        ->reference('$advert')
        ->property('fetcher')
        ->method('getTitle');
      
      // $advert->fetcher->getTitle()    -> fire
      // $this->fetcher->getTitle()      -> skip
      
      
      # 
      # $callback()->userName
      
      

    }

  }
