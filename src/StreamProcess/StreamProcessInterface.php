<?php

  namespace Funivan\PhpTokenizer\StreamProcess;

  use Funivan\PhpTokenizer\Strategy\StrategyInterface;
  use Funivan\PhpTokenizer\Token;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 4/19/15
   */
  interface StreamProcessInterface extends \Iterator {

    /**
     * @param StrategyInterface $strategy
     * @return Token
     */
    public function process(StrategyInterface $strategy);

    /**
     * @return StreamProcess|null
     */
    public function getProcessor();


    /**
     * @return StreamProcess|null
     */
    public function current();

  }