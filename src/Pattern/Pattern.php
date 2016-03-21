<?php

  namespace Funivan\PhpTokenizer\Pattern;

  use Funivan\PhpTokenizer\Collection;


  /**
   * @deprecated
   */
  class Pattern extends PatternMatcher {

    /**
     * @deprecated
     * @inheritdoc
     */
    public function __construct(Collection $collection) {
      trigger_error('Deprecated. Use matcher instead', E_USER_DEPRECATED);
      parent::__construct($collection);
    }


  }