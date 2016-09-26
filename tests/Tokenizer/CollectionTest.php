<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Helper;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/25/13
   */
  class CollectionTest extends \Test\Funivan\PhpTokenizer\MainTestCase {


    public function testBuildFromString() {
      $collection = Collection::createFromString('<?php echo 123;');
      static::assertInstanceOf(Collection::class, $collection);
    }


    public function testGetNext() {
      $collection = Collection::createFromString('<?php echo 123;');
      $nextToken = $collection->getNext();
      static::assertInstanceOf(Token::class, $nextToken);

      $nextToken = $collection->getNext(2);
      static::assertInstanceOf(Token::class, $nextToken);

      $nextToken = $collection->getNext(100);
      static::assertInstanceOf(Token::class, $nextToken);

      static::assertEquals(null, $nextToken->getValue());
    }


    public function testGetPrevious() {
      $collection = Collection::createFromString('<?php echo 123;');

      $previousToken = $collection->getPrevious();
      static::assertInstanceOf(Token::class, $previousToken);

      $previousToken = $collection->getPrevious(2);
      static::assertInstanceOf(Token::class, $previousToken);

      $next = $collection->getNext(100);
      static::assertInstanceOf(Token::class, $next);
      static::assertEquals(null, $next->getValue());

      $previous = $collection->getPrevious(100);
      static::assertInstanceOf(Token::class, $previous);
      static::assertEquals(null, $previous->getValue());
    }


    public function testAssemble() {
      $code = '<?php echo 123;';
      $collection = Collection::createFromString($code);

      static::assertEquals($code, (string) $collection);
    }


    public function testSetToken() {
      $collection = Collection::createFromString('<?php echo 123;');

      $collection[0] = new Token();

      try {
        $collection[10] = null;
        static::fail('Invalid token set. Expect exception.');
      } catch (\Exception $e) {
        static::assertInstanceOf('\Exception', $e);
      }

      $itemsNum = $collection->count();
      $collection[] = new Token();
      static::assertCount($itemsNum + 1, $collection);

    }


    public function testAddTokenAfter() {
      $collection = Collection::createFromString('<?php echo 123;');

      $newToken = new Token();
      $newToken->setValue("echo");

      $collection->addAfter(4, [$newToken]);

      static::assertEquals($newToken, $collection->getLast());

      $exception = null;
      try {
        $collection->addAfter(4, 'test');
      } catch (\Exception $exception) {
      }
      static::assertInstanceOf('Exception', $exception);
    }


    public function testAddCollectionAfter() {
      $collection = Collection::createFromString('<?php echo 123;');

      $otherCollection = Collection::createFromString('<?php echo "test";');
      $otherCollection->getFirst()->remove();
      $otherCollection->refresh();

      $collection->addAfter(4, $otherCollection->getTokens());

      $collection->slice(5);

      static::assertEquals($otherCollection->assemble(), $collection->assemble());

    }


    public function testDump() {
      $collection = Collection::createFromString("<?php echo 123;");
      $dumpString = Helper::dump($collection);
      static::assertContains("<pre>", $dumpString);
      static::assertContains("T_ECHO", $dumpString);

    }


    public function testRefresh() {
      $collection = Collection::createFromString("<?php function();");

      $itemsNum = $collection->count();

      $collection->getLast()->prependToValue(" ");
      static::assertCount($itemsNum, $collection);

      $collection->refresh();
      $itemsNum++;
      static::assertCount($itemsNum, $collection);

    }


    public function testNewCollection() {

      $error = null;
      try {
        $collection = new \Funivan\PhpTokenizer\Collection();
        $collection->setItems([
          'test',
        ]);
      } catch (\Exception $error) {

      }
      static::assertInstanceOf('Exception', $error);
    }


    public function testExtractByTokens() {

      $collection = new Collection();

      $token = new Token();
      $token->setIndex(10);

      $collection->offsetSet(1, $token);


      $first = new Token();
      $first->setIndex(11);
      $collection->append($first);


      $next = new Token();
      $next->setIndex(12);
      $collection->append($next);


      $last = new Token();
      $last->setIndex(19);
      $collection->append($last);


      $token = new Token();
      $token->setIndex(25);
      $collection->append($token);

      $newCollection = $collection->extractByTokens($first, $last);

      static::assertCount(3, $newCollection);
      static::assertEquals($first, $newCollection->getFirst());
      static::assertEquals($next, $newCollection->offsetGet(1));
      static::assertEquals($last, $newCollection->getLast());

    }
  }
