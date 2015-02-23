<?php

  namespace Test\Funivan\PhpTokenizer\Demo;

  /**
   *
   * @author Ivan Shcherbak <dev@funivan.com>
   */
  class ConcatenationOptimize {

    const MAX_ITERATION = 10;

    /**
     * @var \Funivan\PhpTokenizer\File|null
     */
    protected $file = null;

    /**
     * @param \Funivan\PhpTokenizer\File $file
     */
    public function __construct(\Funivan\PhpTokenizer\File $file) {
      $this->file = $file;
      $this->optimize();
    }

    /**
     * @return bool
     */
    protected function optimize() {
      $file = $this->file;
      $collection = $file->getCollection();

      $iteration = static::MAX_ITERATION;

      $this->optimizeVariablesInCurlyBrackets($file);
      do {

        $file->refresh();
        $iteration--;

        $optimizeStringsNum = $collection->query()->typeIs(T_ENCAPSED_AND_WHITESPACE)->getTokensNum();

        # " ,
        # T_ENCAPSED_AND_WHITESPACE
        # T_VARIABLE
        # T_OBJECT_OPERATOR
        # T_STRING
        # T_ENCAPSED_AND_WHITESPACE

        if ($optimizeStringsNum) {

          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs('->');
          $q->strict()->typeIs(T_STRING);
          $q->strict()->valueIs(['"', "'"]);

          $block = $q->getBlock();
          foreach ($block as $col) {
            if ($col->getFirst()->getValue() == $col->getLast()->getValue()) {
              $col->getFirst()->remove();
              $col->getLast()->remove();
            }
          }

          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs(['"', "'"]);

          $block = $q->getBlock();
          foreach ($block as $col) {
            if ($col->getFirst()->getValue() == $col->getLast()->getValue()) {
              $col->getFirst()->remove();
              $col->getLast()->remove();
            }
          }

          # step 1 Object call
          $file->refresh();
          $q = $collection->extendedQuery();
          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->valueIs('->');
          $q->strict()->typeIs(T_STRING);

          $block = $q->getBlock();
          foreach ($block as $col) {
            $delimiter = $col->getFirst();

            $dot = new \Funivan\PhpTokenizer\Token();
            $dot->setValue('.');

            $col->addAfter(1, array(clone $delimiter));
            $col->addAfter(2, array(clone $dot));

            $col->append(clone $dot);
            $col->append(clone $delimiter);

            $col->getFirst()->setValue($col->assemble());
            foreach ($col as $index => $token) {
              if ($index !== 0) {
                $token->remove();
              }
            }
          }

          # step 2 variable
          $file->refresh();
          $q = $collection->extendedQuery();

          $q->strict()->valueIs(['"', "'"]);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
          $q->strict()->typeIs(T_VARIABLE);
          $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);

          $block = $q->getBlock();
          foreach ($block as $col) {

            $delimiter = $col->getFirst();

            $dot = new \Funivan\PhpTokenizer\Token();
            $dot->setValue('.');

            $col->addAfter(1, array(clone $delimiter));
            $col->addAfter(2, array(clone $dot));

            $col->addAfter(4, array(clone $dot));
            $col->addAfter(5, array(clone $delimiter));

            $col->getFirst()->setValue($col->assemble());

            foreach ($col as $index => $token) {

              if ($index !== 0) {
                $token->remove();
              }
            }
          }

        }

      } while (!empty($optimizeStringsNum) and $iteration > 0);

      return true;
    }


    /**
     * From:  "test '{$_GET["d"]}' new";
     * To  :  "test '".$_GET["d"]."' new";
     *
     * @param \Funivan\PhpTokenizer\File $file
     */
    protected function optimizeVariablesInCurlyBrackets(\Funivan\PhpTokenizer\File $file) {

      $file->refresh();

      $collection = $file->getCollection();

      $q = $collection->extendedQuery();

      $q->strict()->valueIs(['"', "'"]);
      $q->strict()->typeIs(T_ENCAPSED_AND_WHITESPACE);
      $q->strict()->valueIs('{');
      $q->search()->valueIs('}');

      $block = $q->getBlock();
      foreach ($block as $col) {
        $open = $col->query()->valueIs('{')->getTokensNum();
        $close = $col->query()->valueIs('}')->getTokensNum();
        if ($open == $close) {
          $delimiter = $col->getFirst()->getValue();
          $col->rewind();
          $curly = $col->getNext(2);
          $curly->setValue($delimiter . '.');
          $col->getLast()->setValue('.' . $delimiter);
        }
      }

    }
  }