<?php

  namespace Funivan\PhpTokenizer\StreamProcess;

  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/19/15
   */
  interface StreamProcess {

    /**
     * @param StrategyInterface $strategy
     * @return Token|null
     */
    public function process(StrategyInterface $strategy);

  }