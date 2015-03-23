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
  class TokenSequence extends SequenceExtractor {

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
    
  }
