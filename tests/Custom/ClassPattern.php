<?php

  namespace Test\Funivan\PhpTokenizer\Custom;

  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 6/26/15
   */
  class ClassPattern {

    /**
     * @var Strict
     */
    private $nameQuery = null;

    public function __construct() {
      $this->nameQuery = Strict::create()->valueLike('!.*!');
    }


    public function nameIs($name) {
      $this->nameQuery = Strict::create()->valueIs($name);
      return $this;
    }

    /**
     * @param StreamProcess $processor
     * @return array
     */
    public function __invoke(StreamProcess $processor) {
      $newCollections = [];

      foreach ($processor as $p) {
        $p->strict('class');
        $p->process($this->nameQuery);
        $body = $p->section('{', '}');
        if ($p->isValid()) {
          $newCollections[] = $body->extractItems(1, -1);
        }
      }

      return $newCollections;
    }

  }