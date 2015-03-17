<?php

  namespace Funivan\PhpTokenizer\Query;

  use Funivan\PhpTokenizer;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  interface QueryInterface {

    /**
     * Check if token is valid for current query
     *
     * @param PhpTokenizer\Token $token
     * @return boolean
     */
    public function isValid(PhpTokenizer\Token $token);

  }