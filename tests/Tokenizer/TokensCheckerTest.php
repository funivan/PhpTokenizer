<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;
  use Funivan\PhpTokenizer\StreamProcess\TokensChecker;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 6/26/15
   */
  class TokensCheckerTest extends MainTestCase {

    /**
     * Prototype for new version
     */
    public function testChecker() {
      $code = '<? class A { public $user = null; }';
      $tokensChecker = new TokensChecker(Collection::initFromString($code));

      $tokensChecker->pattern(function (StreamProcess $processor) {
        $newCollections = [];

        foreach ($processor as $p) {
          $p->strict('class');
          $p->process(Strict::create()->valueLike("!.*!"));
          $body = $p->section('{', '}');
          if ($p->isValid()) {
            $newCollections[] = $body->extractItems(1, -1);
          }
        }

        return $newCollections;
      });

      $this->assertCount(1, $tokensChecker->getCollections());
    }
  }