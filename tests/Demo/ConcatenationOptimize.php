<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class ConcatenationOptimize {

    const MAX_ITERATION = 10;

    /**
     *
     * OLD: echo "$user";
     * NEW: echo $user;
     *
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return bool
     */
    public function removeQuotesNearVariable(\Funivan\PhpTokenizer\Collection $collection) {

      $changed = false;

      return $changed;
    }

    /**
     *
     * OLD: echo "text $user";
     * NEW: echo "text".$user;
     *
     *
     * OLD: echo "custom text: $user or ";
     * NEW: echo "custom text: ".$user." or ";
     *
     *
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return bool
     */
    public function extractVariableFromString(\Funivan\PhpTokenizer\Collection $collection) {

      $changed = false;

      $collection->refresh();
      $q = $collection->extendedQuery();
      $q->strict()->valueIs(['"', "'"]);
      $q->possible()->typeIs(T_ENCAPSED_AND_WHITESPACE);
      $q->strict()->typeIs(T_VARIABLE);
      $q->possible()->typeIs(T_ENCAPSED_AND_WHITESPACE);
      $q->strict()->valueLike('!.*!');

      $block = $q->getBlock();


      foreach ($block as $col) {
        $delimiterValue = $col->getFirst()->getValue();
        $next = $col->getNext(1);
        if ($next->getType() === T_ENCAPSED_AND_WHITESPACE) {
          $next->appendToValue($delimiterValue . ".");
          $changed = true;
        } else {
          $col->getFirst()->appendToValue($delimiterValue . ".");
          $changed = true;
        }

        $lastItems = $col->extractItems(-2);
        $prevItem = $lastItems->getFirst();
        if ($prevItem->getType() === T_ENCAPSED_AND_WHITESPACE) {
          $prevItem->prependToValue("." . $delimiterValue);
          $changed = true;
        } else {
          $col->getLast()->prependToValue("." . $delimiterValue);
          $changed = true;
        }

      }


      return $changed;
    }

    /**
     * OLD: echo $user."";
     * NEW: echo $user;
     *
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return bool
     */
    public function testRemoveEmptyConcatenatedStrings(\Funivan\PhpTokenizer\Collection $collection) {
      $changed = false;

      $removeTokens = function ($token) {
        $token->remove();
      };

      $emptyStringsValue = array("''", '""');


      $collection->refresh();
      $q = $collection->extendedQuery();
      $q->strict()->valueIs(".");
      $q->strict()->valueIs($emptyStringsValue);

      $block = $q->getBlock();
      if ($block->count() > 0) {
        $block->mapCollectionTokens($removeTokens);
        $changed = true;
      }

      $collection->refresh();
      $q = $collection->extendedQuery();

      $q->strict()->valueIs($emptyStringsValue);
      $q->strict()->valueIs(".");

      $block = $q->getBlock();
      if ($block->count() > 0) {
        $block->mapCollectionTokens($removeTokens);
        $changed = true;
      }


      return $changed;
    }


    /**
     * From:  "test {$_GET["d"]} new";
     * To  :  "test ".$_GET["d"]." new";
     *
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return bool
     */
    public function extractVariablesFromCurlyBrackets(\Funivan\PhpTokenizer\Collection $collection) {
      $changed = false;

      $q = $collection->extendedQuery();

      //@todo search condition check
      $q->strict()->valueIs('"');
      $q->possible()->typeIs(T_ENCAPSED_AND_WHITESPACE);
      $q->strict()->valueIs('{');
      $q->strict()->typeIs(T_VARIABLE);
      $q->search()->valueIs('}');
      $q->move(1);

      $block = $q->getBlock();


      foreach ($block as $col) {
        $delimiter = $col->getFirst();
        $delimiterValue = $col->getFirst()->getValue();


        $next = $col->getNext(1);
        if ($next->getType() === T_ENCAPSED_AND_WHITESPACE) {
          $col->getNext(2)->setValue($delimiterValue . ".");
        } else {
          $delimiter->remove();
          $next->remove();
        }

        $col->getLast()->setValue("." . $delimiterValue);
      }


      # optimize blocks with start of "{$name} custom text"
      //$q = $collection->extendedQuery();
      //$q->strict()->typeIs(T_WHITESPACE);
      //$q->strict()->valueIs('"');
      //$q->strict()->valueIs('{');
      //$q->search()->valueIs('}');
      //
      //$block = $q->getBlock();
      //
      //
      //foreach ($block as $col) {
      //  # simple validation
      //
      //  $open = $col->query()->valueIs('{')->getTokensNum();
      //  $close = $col->query()->valueIs('}')->getTokensNum();
      //
      //  if ($open != $close) {
      //    continue;
      //  }
      //
      //  $delimiter = $col->getFirst();
      //  echo "\n***".__LINE__."***\n<pre>".print_r($col, true)."</pre>\n";die();
      //  $lastItem = $col->getLast();
      //
      //  $lastItem->setValue("." . $delimiter->getValue());
      //  //$col->offsetGet(2)->remove();
      //  //
      //  //$delimiter->remove();
      //  //$col->refresh();
      //  $changed = true;
      //}
      //
      //

      return $changed;
    }

  }