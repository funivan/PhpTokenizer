<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\QuerySequence;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Strategy\Possible;
use Funivan\PhpTokenizer\Strategy\Search;
use Funivan\PhpTokenizer\Strategy\Strict;
use Funivan\PhpTokenizer\Token;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use stdClass;

class QuerySequenceTest extends TestCase
{
    public function testSimpleIterate(): void
    {
        $code = '<?php 
      echo $a;
      echo $a;
      echo $a;
      ';
        $collection = Collection::createFromString($code);

        $findItems = [];
        foreach ($collection as $index => $token) {
            $querySequence = new QuerySequence($collection, $index);
            $token = $querySequence->strict('echo');
            if ($querySequence->isValid()) {
                $findItems[] = $token;
            }
        }

        static::assertCount(3, $findItems);
    }

    public function testMoveToToken(): void
    {
        $code = '<?php echo $a;';
        $collection = Collection::createFromString($code);
        $lastToken = $collection->getLast();

        $finder = new QuerySequence($collection);
        $token = $finder->moveToToken($lastToken);
        static::assertEquals($lastToken, $token);

        $finder = new QuerySequence($collection);
        $token = $finder->moveToToken(new Token());
        static::assertNull($token->getValue());
        static::assertFalse($finder->isValid());
    }

    /**
     * @return array
     */
    public function getTestStrictInvalidConditionDataProvider()
    {
        return [
            [
                new stdClass(),
            ],

            [
                new Possible(),
            ],
            [
                [],
            ],

        ];
    }

    /**
     * @dataProvider getTestStrictInvalidConditionDataProvider
     */
    public function testStrictInvalidCondition($condition): void
    {
        $code = '<?php echo $a;';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 3);
        $this->expectException(InvalidArgumentException::class);
        $q->strict($condition);
    }

    /**
     * @return array
     */
    public function getTestStrictConditionDataProvider()
    {
        return [
            [
                T_VARIABLE,
                true,
            ],

            [
                T_WHITESPACE,
                false,
            ],

            [
                null,
                false,
            ],

            [
                '$a',
                true,
            ],

            [
                '$b',
                false,
            ],

            [
                Strict::create()->valueLike('!^\$.*!'),
                true,
            ],

            [
                Strict::create()->valueLike('!^\$.*!')->typeIs(T_WHITESPACE),
                false,
            ],

        ];
    }

    /**
     * @dataProvider getTestStrictConditionDataProvider
     */
    public function testStrictCondition($condition, $isValid): void
    {
        $code = '<?php echo $a;';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 3);
        $q->strict($condition);
        static::assertEquals($isValid, $q->isValid());
    }

    /**
     * @return array
     */
    public function getTestPossibleConditionDataProvider()
    {
        return [
            [
                T_VARIABLE,
                true,
            ],
            [
                T_WHITESPACE,
                false,
            ],
            [
                null,
                false,
            ],

            [
                '$a',
                true,
            ],

            [
                '$b',
                false,
            ],

            [
                Possible::create()->valueLike('!^\$.*!'),
                true,
            ],

            [
                Possible::create()->valueLike('!^\$.*!')->typeIs(T_WHITESPACE),
                false,
            ],

        ];
    }

    /**
     * @dataProvider getTestPossibleConditionDataProvider
     */
    public function testPossibleCondition($condition, $isValidToken): void
    {
        $code = '<?php echo $a;';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 3);
        $token = $q->possible($condition);

        static::assertEquals($isValidToken, $token->isValid());
        static::assertTrue($q->isValid());
    }

    /**
     * @return array
     */
    public function getTestPossibleInvalidConditionDataProvider()
    {
        return [
            [
                new stdClass(),
            ],

            [
                new Strict(),
            ],
            [
                [],
            ],
            [
                Search::create(),
            ],

        ];
    }

    /**
     * @dataProvider getTestPossibleInvalidConditionDataProvider
     */
    public function testPossibleInvalidCondition($condition): void
    {
        $code = '<?php echo $a;';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 3);
        $this->expectException(InvalidArgumentException::class);
        $q->possible($condition);
    }

    public function testSectionWithoutEndDelimiter(): void
    {
        $code = '<?php foreach($users as $user ){ $a;';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 0);
        $section = $q->section('{', '}');

        static::assertCount(0, $section);
    }

    /**
     *  Check move strategy
     */
    public function testMove(): void
    {
        $code = '<?php echo 1';
        $collection = Collection::createFromString($code);

        $q = new QuerySequence($collection, 0);

        $token = $q->move(1);
        static::assertEquals('echo', $token->getValue());
        static::assertTrue($q->isValid());

        $token = $q->move(2);
        static::assertEquals('1', $token->getValue());
        static::assertTrue($q->isValid());

        $token = $q->move(-2);
        static::assertEquals('echo', $token->getValue());
        static::assertTrue($q->isValid());

        $token = $q->move(-100);
        static::assertNull($token->getValue());
        static::assertFalse($q->isValid());
    }

    public function testSetValidWithInvalidFlag(): void
    {
        $q = new QuerySequence(new Collection(), 0);
        /** @noinspection PhpParamsInspection */
        $this->expectException(InvalidArgumentException::class);
        $q->setValid(new stdClass());
    }
}
