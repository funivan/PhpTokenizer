<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Exception\Exception;
use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
use Funivan\PhpTokenizer\Query\Query;
use Funivan\PhpTokenizer\Token;
use Funivan\PhpTokenizer\TokenFinder;
use PHPUnit\Framework\TestCase;
use stdClass;

class QueryTest extends TestCase
{
    public function testType(): void
    {
        $collection = Collection::createFromString('<?php echo $user;');
        $finder = new TokenFinder($collection);

        $query = new Query();
        $query->typeIs(T_ECHO);
        static::assertCount(1, $finder->find($query));

        $query = new Query();
        $query->typeIs([T_ECHO, T_VARIABLE]);
        static::assertCount(2, $finder->find($query));

        $query = new Query();
        $query->typeNot(T_ECHO);
        static::assertCount(count($collection) - 1, $finder->find($query));
    }

    public function testValue(): void
    {
        $collection = Collection::createFromString('<?php 
        echo 1; 
      
      ');

        $query = new Query();
        $query->valueNot('echo');
        static::assertCount($collection->count() - 1, $collection->find($query));

        $collection = Collection::createFromString('<?php echo "123"; echo "132";');
        static::assertCount(4, $collection->find(Query::create()->valueIs(['echo', ';'])));

        $q = Query::create();
        static::assertCount(2, $collection->find($q->valueLike('/\d+/')));

        static::assertCount(0, $collection->find($q->valueLike(null)));

        static::assertCount(0, $collection->find($q->valueIs(null)));
    }

    public function testWithInvalidValue(): void
    {
        $query = new Query();
        $this->expectException(InvalidArgumentException::class);
        $query->valueNot(new stdClass());
    }

    public function testWithInvalidArrayValue(): void
    {
        $query = new Query();
        $this->expectException(InvalidArgumentException::class);
        $query->valueNot([new stdClass()]);
    }

    public function testQueryWithoutConditions(): void
    {
        $query = new Query();
        $token = new Token();
        static::assertTrue($query->isValid($token));
    }

    public function testIndexIs(): void
    {
        $query = new Query();
        $query->indexIs(1);
        $token = new Token();
        $token->setIndex(1);

        static::assertTrue($query->isValid($token));
        $token->setIndex(3);
        static::assertFalse($query->isValid($token));
    }

    public function testIndexIsMultipleDefinition(): void
    {
        $query = new Query();
        $query->indexIs([4, 5, 6, 1]);
        $token = new Token();
        $token->setIndex(1);

        static::assertTrue($query->isValid($token));
        $token->setIndex(10);
        static::assertFalse($query->isValid($token));
    }

    public function testIndexNot(): void
    {
        $query = new Query();
        $query->indexIs(1);
        $token = new Token();
        $token->setIndex(10);

        static::assertFalse($query->isValid($token));
        $token->setIndex(1);
        static::assertTrue($query->isValid($token));
    }

    public function testIndexNotMultipleDefinition(): void
    {
        $query = new Query();
        $query->indexNot([4, 5, 6, 1]);
        $token = new Token();
        $token->setIndex(2);

        static::assertTrue($query->isValid($token));

        $token->setIndex(4);
        static::assertFalse($query->isValid($token));
    }

    public function testLt(): void
    {
        $query = new Query();
        $query->indexLt(10);
        $token = new Token();
        $token->setIndex(10);

        static::assertFalse($query->isValid($token));
        $token->setIndex(8);
        static::assertTrue($query->isValid($token));

        $query = new Query();
        $query->indexLt([40, 30, 20]);
        static::assertTrue($query->isValid($token));

        $token->setIndex(35);
        static::assertFalse($query->isValid($token));
    }

    public function testGt(): void
    {
        $query = new Query();
        $query->indexGt(10);

        $token = new Token();
        $token->setIndex(10);

        static::assertFalse($query->isValid($token));

        $token->setIndex(11);
        static::assertTrue($query->isValid($token));

        $query = new Query();
        $query->indexGt([40, 30, 20]);
        static::assertFalse($query->isValid($token));

        $token->setIndex(21);
        static::assertFalse($query->isValid($token));

        $token->setIndex(40);
        static::assertFalse($query->isValid($token));

        $token->setIndex(41);
        static::assertTrue($query->isValid($token));
    }

    public function testPrepareNullValueCondition(): void
    {
        $q = new Query();
        $q->valueIs(null);

        $token = new Token();

        static::assertFalse($q->isValid($token));
    }

    /**
     * @throws Exception
     */
    public function testPrepareNullIntValues(): void
    {
        $q = new Query();
        $q->typeIs(null);

        $token = new Token();

        static::assertFalse($q->isValid($token));
    }

    public function testPrepareObjectIntValues(): void
    {
        $q = new Query();
        /** @noinspection PhpParamsInspection */
        $this->expectException(InvalidArgumentException::class);
        $q->typeIs(new stdClass());
    }

    public function testPrepareArrayOfInvalidValuesForIntValueCondition(): void
    {
        $q = new Query();
        $this->expectException(InvalidArgumentException::class);
        $q->typeIs([null]);
    }

    public function testCustomCallback(): void
    {
        $q = new Query();
        $q->custom(fn (Token $token) => $token->isValid() and $token->getLine() < 10);

        $token = new Token();
        static::assertFalse($q->isValid($token));

        $token = new Token();
        $token->setValue("test");
        $token->setLine(5);
        static::assertTrue($q->isValid($token));
    }

    public function testCustomInvalidCallback(): void
    {
        $q = new Query();
        $q->custom(fn (Token $token) => $token);
        $this->expectException(Exception::class);
        $q->isValid(new Token());
    }
}
