<?php

  namespace Funivan\PhpTokenizer\BlockExtractor;

  use Funivan\PhpTokenizer\Query\QueryProcessor\QueryProcessorInterface;

  abstract class ExtractProcessor implements QueryProcessorInterface {

    /**
     * @var null|int
     */
    protected $startIndex = null;

    /**
     * @var null|int
     */
    protected $endIndex = null;

    /**
     * @var null|int
     */
    protected $nextTokenIndexForCheck = null;

    /**
     * @return null|int
     */
    public function getStartIndex() {
      return $this->startIndex;
    }

    /**
     * @return null|int
     */
    public function getEndIndex() {
      return $this->endIndex;
    }

    /**
     * @return int|null
     */
    public function getNextTokenIndexForCheck() {
      return $this->nextTokenIndexForCheck;
    }

  }