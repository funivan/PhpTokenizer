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
      $this->assertInstanceOf(Collection::N, $collection);
    }


    public function testGetNext() {
      $collection = Collection::createFromString('<?php echo 123;');
      $nextToken = $collection->getNext();
      $this->assertInstanceOf(Token::N, $nextToken);

      $nextToken = $collection->getNext(2);
      $this->assertInstanceOf(Token::N, $nextToken);

      $nextToken = $collection->getNext(100);
      $this->assertInstanceOf(Token::N, $nextToken);

      $this->assertEquals(null, $nextToken->getValue());
    }


    public function testGetPrevious() {
      $collection = Collection::createFromString('<?php echo 123;');

      $previousToken = $collection->getPrevious();
      $this->assertInstanceOf(Token::N, $previousToken);

      $previousToken = $collection->getPrevious(2);
      $this->assertInstanceOf(Token::N, $previousToken);

      $next = $collection->getNext(100);
      $this->assertInstanceOf(Token::N, $next);
      $this->assertEquals(null, $next->getValue());

      $previous = $collection->getPrevious(100);
      $this->assertInstanceOf(Token::N, $previous);
      $this->assertEquals(null, $previous->getValue());
    }


    public function testAssemble() {
      $code = '<?php echo 123;';
      $collection = Collection::createFromString($code);

      $this->assertEquals($code, (string) $collection);
    }


    public function testSetToken() {
      $collection = Collection::createFromString('<?php echo 123;');

      $collection[0] = new Token();

      try {
        $collection[10] = null;
        $this->fail('Invalid token set. Expect exception.');
      } catch (\Exception $e) {
        $this->assertInstanceOf('\Exception', $e);
      }

      $itemsNum = $collection->count();
      $collection[] = new Token();
      $this->assertCount($itemsNum + 1, $collection);

    }


    public function testAddTokenAfter() {
      $collection = Collection::createFromString('<?php echo 123;');

      $newToken = new Token();
      $newToken->setValue("echo");

      $collection->addAfter(4, [$newToken]);

      $this->assertEquals($newToken, $collection->getLast());

      $exception = null;
      try {
        $collection->addAfter(4, 'test');
      } catch (\Exception $exception) {
      }
      $this->assertInstanceOf('Exception', $exception);
    }


    public function testAddCollectionAfter() {
      $collection = Collection::createFromString('<?php echo 123;');

      $otherCollection = Collection::createFromString('<?php echo "test";');
      $otherCollection->getFirst()->remove();
      $otherCollection->refresh();

      $collection->addAfter(4, $otherCollection->getItems());

      $collection->slice(5);

      $this->assertEquals($otherCollection->assemble(), $collection->assemble());

    }


    public function testDump() {
      $collection = Collection::createFromString("<?php echo 123;");
      $dumpString = Helper::dump($collection);
      $this->assertContains("<pre>", $dumpString);
      $this->assertContains("T_ECHO", $dumpString);

    }


    public function testRefresh() {
      $collection = Collection::createFromString("<?php function();");

      $itemsNum = $collection->count();

      $collection->getLast()->prependToValue(" ");
      $this->assertCount($itemsNum, $collection);

      $collection->refresh();
      $itemsNum++;
      $this->assertCount($itemsNum, $collection);

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
      $this->assertInstanceOf('Exception', $error);
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

      $this->assertCount(3, $newCollection);
      $this->assertEquals($first, $newCollection->getFirst());
      $this->assertEquals($next, $newCollection->offsetGet(1));
      $this->assertEquals($last, $newCollection->getLast());

    }
  }
