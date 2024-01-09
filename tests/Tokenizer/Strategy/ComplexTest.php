<?php

declare(strict_types=1);

namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

use Exception;
use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Strategy\Possible;
use Funivan\PhpTokenizer\Token;
use PHPUnit\Framework\TestCase;

class ComplexTest extends TestCase
{
    public function testSkipWhitespaces(): void
    {
        $code = '<?php
      echo $a;
      echo $a  ;
      echo $a
      
      ;
      ';
        $collection = Collection::createFromString($code);

        $findItems = [];
        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $q->setSkipWhitespaces(true);

            $list = $q->sequence(['echo', '$a', ';']);
            if ($q->isValid()) {
                $findItems[] = $list;
            }
        }

        static::assertCount(3, $findItems);
    }

    /**
     * @return array
     */
    public function getTestWithoutWhitespaceSkipDataProvider()
    {
        return [
            [
                'sequence' => ['echo', '$a', ';'],
                'items' => 0,
            ],

            [
                'sequence' => ['$a', ';'],
                'items' => 1,
            ],

            [
                'sequence' => ['echo', ' ', '$a', '  ', ';'],
                'items' => 1,
            ],

            [
                'sequence' => ['echo', T_WHITESPACE, '$a', T_WHITESPACE, ';'],
                'items' => 2,
            ],
        ];
    }

    /**
     * @dataProvider getTestWithoutWhitespaceSkipDataProvider
     */
    public function testWithoutWhitespaceSkip($sequence, $expectItems): void
    {
        $code = '<?php 
      echo $a;
      echo $a  ;
      echo $a
      
      ;
      ';

        $collection = Collection::createFromString($code);

        $findItems = [];
        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $list = $q->sequence($sequence);
            if ($q->isValid()) {
                $findItems[] = $list;
            }
        }

        static::assertCount($expectItems, $findItems);
    }

    /**
     * @return array
     */
    public function getComplexTestData()
    {
        return [
            [
                'if (!is_array($a1)){
            $a1 = (array) $a1;
          }',
                'containt' => '$a1 = (array) $a1;',
                'notContain' => 'is_array',
            ],
            [
                'if ( is_array( $a2 )==false){
              $a2 = (array) 
              $a2;
          }
          ',
                'containt' => '$a2 = (array) $a2;',
                'notContain' => 'is_array',
            ],
            [
                ' if (!is_array($a4) == false){
          $a4 = (array) $a4;
          }
        ',
                'containt' => 'is_array',
            ],
            [
                'if (is_array($a5)!=true){
          $a5 = (array) $a5;
          }',
                'containt' => '$a5 = (array) $a5;',
                'notContain' => 'is_array',
            ],
            [
                ' if (is_array($a6) !==
      true){
          $a6 = (array) $a6;
          }',
                'containt' => '$a6 = (array) $a6;',
                'notContain' => 'is_array',
            ],
            ['    if (is_array($a7)==true){
          $a7 = (array) $a7;
        }',
                'containt' => 'is_array',
            ],
            [
                ' if (is_array($a8) === false){
            $a8 = (array) $a8;
          }
        ',
                'containt' => '$a8 = (array) $a8;',
                'notContain' => 'is_array',
            ],
        ];
    }

    /**
     * @dataProvider   getComplexTestData
     * @throws Exception
     */
    public function testComplex(string $code, string $contain = null, $notContain = null): void
    {
        if ($contain == null and $notContain === null) {
            throw new Exception('Please provide notContain or contain condition');
        }
        $code = '<?php ' . $code;
        $collection = Collection::createFromString($code);
        foreach ($collection as $index => $token) {
            $q = new QuerySequence($collection, $index);
            $q->setSkipWhitespaces(true);
            $start = $q->sequence(['if', '(', Possible::create()->valueIs('!'), 'is_array', '(']);
            $token = $q->strict(T_VARIABLE);
            $q->strict(')');
            if ($q->isValid() and $start[2]->isValid() == false) {
                if ($q->process(Possible::create()->valueIs(['==', '===']))->isValid()) {
                    $q->strict('false');
                } elseif ($q->process(Possible::create()->valueIs(['!=', '!==']))->isValid()) {
                    $q->strict('true');
                }
            }
            $s = [')', '{', $token->getValue(), '=', '(array)', $token->getValue(), ';', '}'];
            $last = $q->sequence($s);
            if ($q->isValid()) {
                $start = $start->getFirst()->getIndex();
                $end = $last->getLast()->getIndex();
                $newToken = new Token();
                $newToken->setValue($token->getValue() . ' = (array) ' . $token->getValue() . ';');
                foreach ($collection as $collectionIndex => $collectionToken) {
                    if ($collectionIndex >= $start and $collectionIndex <= $end) {
                        $collectionToken->remove();
                    }
                }
                $collection[$end] = $newToken;
            }
        }
        $result = (string) $collection;
        if (is_string($contain)) {
            static::assertStringContainsString($contain, $result);
        }

        if (is_string($notContain)) {
            static::assertStringNotContainsString($notContain, $result);
        }
    }
}
