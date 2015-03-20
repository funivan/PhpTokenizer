<?php

  namespace Funivan\PhpTokenizer\Extractor;

  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryProcessor\Move;
  use Funivan\PhpTokenizer\Query\QueryProcessor\Possible;
  use Funivan\PhpTokenizer\Query\QueryProcessor\QueryProcessorInterface;
  use Funivan\PhpTokenizer\Query\QueryProcessor\Strict;

  /**
   * Extended query used for parsing blocks of tokens
   *
   * @author  Ivan Shcherbak <dev@funivan.com>
   * @package Funivan\PhpTokenizer\Query\Query
   */
  class TokenSequence extends TokenSequenceExtractor {

    /**
     * @var QueryProcessorInterface[]
     */
    protected $processors = null;

    /**
     * @return Query
     */
    public function strict() {
      $query = new Query();
      $processor = new Strict($query);
      $this->addProcessor($processor);
      return $query;
    }

    /**
     * @param QueryProcessorInterface $processor
     * @return $this
     */
    public function addProcessor(QueryProcessorInterface $processor) {
      $this->processors[] = $processor;
      return $this;
    }

    /**
     * @return Query
     */
    public function possible() {
      $query = new Query();
      $processor = new Possible($query);
      $this->addProcessor($processor);
      return $query;
    }

    /**
     * @param int $direction
     * @param int $steps
     * @return Move
     */
    public function move($direction, $steps) {
      $processor = new Move($direction, $steps);
      $this->addProcessor($processor);
      return $processor;
    }

    public function testExtract(\Funivan\PhpTokenizer\Collection $collection, $name = null) {

      $block = new \Funivan\PhpTokenizer\Block();

      $tokensNum = $collection->count();

      $nextTokenIndexForCheck = 0;
      while ($nextTokenIndexForCheck <= $tokensNum) {

        $startSectionIndex = null;
        $endSectionIndex = null;

        echo __LINE__ . "*** |\n\n check index:" . $nextTokenIndexForCheck . "\n";

        $startNextTokenIndexToCheck = $nextTokenIndexForCheck;

        foreach ($this->processors as $query) {

          $result = $query->process($collection, $startNextTokenIndexToCheck);


          echo __LINE__ . "*** | \n";
          echo __LINE__ . "*** | Process token:|" . $collection[$startNextTokenIndexToCheck] . "|\n";
          echo __LINE__ . "*** | Current index: " . $startNextTokenIndexToCheck . "\n";
          echo __LINE__ . "*** | Query :" . get_class($query) . "\n";

          if (!$result->isValid()) {
            echo __LINE__ . "*** | no valid \n";
            $startSectionIndex = null;
            $endSectionIndex = null;
            break;
          }

          echo __LINE__ . "*** | valid  \n";


          echo "\n***" . __LINE__ . "***\n<pre>" . print_r($result, true) . "</pre>\n";
          $startNextTokenIndexToCheck = $result->getNextTokenIndexForCheck();
          echo __LINE__ . "*** | From result nextTokenIndexForCheck:" . $startNextTokenIndexToCheck . "\n";

          $endIndex = $result->getEndIndex();

          if ($endIndex !== null or $endSectionIndex == null) {
            $endSectionIndex = $endIndex;
          }

          $startIndex = $result->getStartIndex();
          if ($startIndex !== null or $startSectionIndex === null) {
            $startSectionIndex = $startIndex;
          }

          if ($endSectionIndex == null and $startSectionIndex != null) {
            $endSectionIndex = $startSectionIndex;
          }

          if ($startSectionIndex == null and $endSectionIndex != null) {
            $startSectionIndex = $endSectionIndex;
          }

          echo __LINE__ . "*** | startSectionIndex:" . $startSectionIndex . "\n";
          echo __LINE__ . "*** | endSectionIndex  :" . $endSectionIndex . "\n";

        }

        echo __LINE__ . "*** | got startSectionIndex:" . $startSectionIndex . "\n";
        echo __LINE__ . "*** | got endSectionIndex:" . $endSectionIndex . "\n";

        if ($startSectionIndex !== null and $endSectionIndex !== null) {
          echo __LINE__ . "*** | " . "" . "\n";
          echo __LINE__ . "*** | start index: " . $startSectionIndex . "\n";
          echo __LINE__ . "*** | end index: " . $endSectionIndex . "\n";


          echo __LINE__ . "*** | start extract from : " . $startSectionIndex . "\n";
          echo __LINE__ . "*** | size: " . ($endSectionIndex - $startSectionIndex + 1) . "\n";

          // 3=3 =>1
          // 3=4 => 2

          // 2=2 => 1
          // 2=3 => 2
          // 2=4 => 3


          $items = $collection->extractItems($startSectionIndex, ($endSectionIndex - $startSectionIndex + 1));
          $block->append($items);

        }

        $nextTokenIndexForCheck++;
      }

      return $block;
    }

  }
