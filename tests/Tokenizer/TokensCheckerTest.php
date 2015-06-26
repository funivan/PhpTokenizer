<?php

  namespace Test\Funivan\PhpTokenizer\Tokenizer;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Exception\Exception;
  use Funivan\PhpTokenizer\Strategy\Strict;
  use Funivan\PhpTokenizer\StreamProcess\StreamProcess;
  use Funivan\PhpTokenizer\StreamProcess\TokensChecker;
  use Test\Funivan\PhpTokenizer\Custom\ClassPattern;
  use Test\Funivan\PhpTokenizer\MainTestCase;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 6/26/15
   */
  class TokensCheckerTest extends MainTestCase {

    /**
     * Prototype for new version
     */
    public function testWithCallbackPattern() {
      $code = '<?php class A { public $user = null; }';
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

    public function testWithClassPattern() {
      $code = '<?php class A { public $user = null; } class customUser { }';
      $tokensChecker = new TokensChecker(Collection::initFromString($code));
      $tokensChecker->pattern(new ClassPattern());

      $this->assertCount(2, $tokensChecker->getCollections());

      $tokensChecker = new TokensChecker(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('B');
      $tokensChecker->pattern($classPattern);

      $this->assertCount(0, $tokensChecker->getCollections());

      $tokensChecker = new TokensChecker(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('A');
      $tokensChecker->pattern($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());

      $tokensChecker = new TokensChecker(Collection::initFromString($code));
      $classPattern = new ClassPattern();
      $classPattern->nameIs('customUser');
      $tokensChecker->pattern($classPattern);

      $this->assertCount(1, $tokensChecker->getCollections());
    }


    public function testWithNestedPatterns() {
      # find class with property 
      $code = '<?php class A { public $user = null; static $name;} class customUser { $value; }';
      $tokensChecker = new TokensChecker(Collection::initFromString($code));
      $tokensChecker->pattern(new ClassPattern())
        ->pattern(function (StreamProcess $process) {

          $result = array();
          foreach ($process as $p) {

            $p->process(Strict::create()->valueIs(
              array(
                'public',
                'protected',
                'private',
                'static',
              )
            ));

            $name = $p->strict(T_VARIABLE);
            if ($p->isValid()) {
              $result[] = new Collection(array($name));
            }

          }

          return $result;
        });

      $collections = $tokensChecker->getCollections();
      $this->assertCount(2, $collections);

      $this->assertEquals('$user', (string) $collections[0]);
      $this->assertEquals('$name', (string) $collections[1]);

    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResult() {
      $tokensChecker = new TokensChecker(Collection::initFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->pattern(function (StreamProcess $process) {
        return new \stdClass();
      });

    }

    /**
     * @expectedException Exception
     */
    public function testInvalidPatternResultArray() {
      $tokensChecker = new TokensChecker(Collection::initFromString('<?php echo 1;'));
      /** @noinspection PhpUnusedParameterInspection */
      $tokensChecker->pattern(function (StreamProcess $process) {
        return array(new \stdClass());
      });

    }
    

  }