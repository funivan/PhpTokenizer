<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

use PHPUnit\Framework\Attributes\DataProvider;
use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\Query\Query;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Strategy\Section;
use Funivan\PhpTokenizer\Strategy\Strict;
use PHPUnit\Framework\TestCase;

class SectionTest extends TestCase
{
    /**
     * @return array
     */
    public static function functionCallDataProvider()
    {
        return [
            [
                'header(123); ',
                'header',
                'header(123)',
            ],
            [
                'echo header          (123, 432); ',
                'header',
                'header          (123, 432)',
            ],

            [
                'echo header          (123, 432); ',
                'header',
                'header          (123, 432)',
            ],

        ];
    }

    #[DataProvider('functionCallDataProvider')]
    public function testFunctionCall($code, $functionName, $expectCode): void
    {
        $code = '<?php ' . $code;

        $collection = Collection::createFromString($code);

        $lines = [];

        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $start = $q->strict($functionName);
            $q->possible(T_WHITESPACE);
            $end = $q->section('(', ')');

            if ($q->isValid()) {
                $lines[] = $collection->extractByTokens($start, $end->getLast());
            }
        }

        static::assertCount(1, $lines);
        static::assertEquals($expectCode, $lines[0]);
    }

    public function testWithEmptySection(): void
    {
        $code = '<?php 
      
      header(123);
      
      return;
      
      ';

        $collection = Collection::createFromString($code);
        $linesWithEcho = [];

        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $start = $q->strict('return');
            $end = $q->section('(', ')');

            if ($q->isValid()) {
                $linesWithEcho[] = $collection->extractByTokens($start, $end->getLast());
            }
        }

        static::assertCount(0, $linesWithEcho);
    }

    public function testWithEmptySectionSearch(): void
    {
        $code = '<?php 
      
      header(123);
      
      return;
      
      ';

        $collection = Collection::createFromString($code);

        $linesWithEcho = [];

        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $start = $q->strict('return');
            $lastToken = $q->process(Section::create()->setDelimiters('(', ')'));

            if ($q->isValid()) {
                $linesWithEcho[] = $collection->extractByTokens($start, $lastToken);
            }
        }

        static::assertCount(0, $linesWithEcho);
    }

    public function testWithMultipleTokens(): void
    {
        $code = '<?php 
      
      class User { 
        abstract function getInfo();

        public function save() {}
      }
      ';

        $collection = Collection::createFromString($code);

        $num = 0;

        (new PatternMatcher($collection))->apply(function (QuerySequence $q) use (&$num): void {
            $q->strict(')');
            $q->possible(T_WHITESPACE);
            $q->section('{', '}');

            if ($q->isValid()) {
                $num++;
            }
        });

        static::assertEquals(1, $num);
    }

    /**
     * @return array
     */
    public static function functionDetectDataProvider()
    {
        return [
            [
                function (QuerySequence $q): void {
                    $q->strict(')');
                    $q->possible(T_WHITESPACE);
                    $q->section('{', '}');
                },
                2,
            ],
            [
                function (QuerySequence $q): void {
                    $q->strict(')');
                    $q->section('{', '}');
                },
                1,
            ],
            [
                function (QuerySequence $q): void {
                    $q->setSkipWhitespaces(true);
                    $q->strict(')');
                    $q->section('{', '}');
                },
                2,
            ],
            [
                function (QuerySequence $q): void {
                    $q->setSkipWhitespaces(true);
                    $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
                    $q->section('(', ')');
                    $q->section('{', '}');
                },
                2,
            ],
            [
                function (QuerySequence $q): void {
                    $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
                    $q->section('(', ')');
                    $q->section('{', '}');
                },
                0,
            ],
            [
                function (QuerySequence $q): void {
                    $q->strict(Strict::create()->valueLike('!^[a-z]+$!i'));
                    $q->strict(T_WHITESPACE);
                    $q->section('(', ')');
                    $q->section('{', '}');
                },
                1,
            ],
        ];
    }

    #[DataProvider('functionDetectDataProvider')]
    public function testFunctionDetect(callable $callback, $expectFunctionNum): void
    {
        $code = '<?php 
      function getInfo ($df){}
      function save() {}
      ';

        $collection = Collection::createFromString($code);

        $num = 0;

        (new PatternMatcher($collection))->apply(function (QuerySequence $q) use ($callback, &$num): void {
            $callback($q);

            if ($q->isValid()) {
                $num++;
            }
        });

        static::assertEquals($expectFunctionNum, $num);
    }

    public function testInvalidSectionStartDefinition(): void
    {
        $section = new Section();
        $this->expectException(InvalidArgumentException::class);
        $section->process(new Collection(), 0);
    }

    public function testInvalidSectionEndDefinition(): void
    {
        $section = new Section();
        $section->setStartQuery(new Query());
        $this->expectException(InvalidArgumentException::class);
        $section->process(new Collection(), 0);
    }
}
