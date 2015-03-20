<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/11/15
   */
  class TokenSequenceExtractor implements ExtractorInterface {

    protected $childName;

    /**
     * @var null|ExtractorInterface
     */
    protected $child = null;

    public function create() {
      return new static();
    }

    /**
     * @param ExtractorInterface $extractor
     * @param null|string $name
     * @return mixed
     */
    public function with(ExtractorInterface $extractor = null, $name = null) {

      $this->child = $extractor;
      $this->childName = $name;


      return $this;
    }


    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param null|string $name
     * @return \Funivan\PhpTokenizer\Block
     */
    public function extract(\Funivan\PhpTokenizer\Collection $collection, $name = null) {

      $ranges = $this->getRangeList($collection);

      $block = new \Funivan\PhpTokenizer\Block();

      foreach ($ranges as $rangeInfo) {
        $start = $rangeInfo[0];
        $length = $rangeInfo[1];
        $items = $collection->extractItems($start, $length);
        $block->append($items);
      }

      $rangesAll = [];

      if (!empty($this->child)) {
        foreach ($block as $index => $blockCollection) {
          $ranges = $this->child->getRangeList($blockCollection);
          echo "\n***" . __LINE__ . "***\n<pre>blockCollection:" . print_r((string) $blockCollection, true) . "</pre>\n";
          echo "\n***" . __LINE__ . "***\n<pre>" . print_r($ranges, true) . "</pre>\n";

          if (empty($ranges)) {
            unset($block[$index]);
            continue;
          }

          if ($name == $this->childName) {
            unset($block[$index]);
            $rangesAll = array_merge($rangesAll, $ranges);
          }
        }
      }


      if (!empty($rangesAll)) {

        foreach ($rangesAll as $rangeInfo) {
          $start = $rangeInfo[0];
          $length = $rangeInfo[1];

          $items = $collection->extractItems($start, $length);
          $block->append($items);
        }

        //echo "\n***".__LINE__."***\n<pre>".print_r($block, true)."</pre>\n";die();
      }

      $block->rewind();
      return $block;

    }

    private function getRangeList($collection) {
      $rangeList = array();


      $tokensNum = $collection->count();

      $nextTokenIndexForCheck = 0;
      while ($nextTokenIndexForCheck <= $tokensNum) {

        $startSectionIndex = null;
        $endSectionIndex = null;

        //echo __LINE__ . "*** |\n\n check index:" . $nextTokenIndexForCheck . "\n";

        $startNextTokenIndexToCheck = $nextTokenIndexForCheck;

        foreach ($this->processors as $query) {

          $result = $query->process($collection, $startNextTokenIndexToCheck);


          //echo __LINE__ . "*** | \n";
          //echo __LINE__ . "*** | Process token:|" . $collection[$startNextTokenIndexToCheck] . "|\n";
          //echo __LINE__ . "*** | Current index: " . $startNextTokenIndexToCheck . "\n";
          //echo __LINE__ . "*** | Query :" . get_class($query) . "\n";

          if (!$result->isValid()) {
            //echo __LINE__ . "*** | no valid \n";
            $startSectionIndex = null;
            $endSectionIndex = null;
            break;
          }

          //echo __LINE__ . "*** | valid  \n";


          //echo "\n***" . __LINE__ . "***\n<pre>" . print_r($result, true) . "</pre>\n";
          $startNextTokenIndexToCheck = $result->getNextTokenIndexForCheck();
          //echo __LINE__ . "*** | From result nextTokenIndexForCheck:" . $startNextTokenIndexToCheck . "\n";

          $endIndex = $result->getEndIndex();

          if ($endIndex !== null or $endSectionIndex == null) {
            $endSectionIndex = $endIndex;
          }

          $startIndex = $result->getStartIndex();
          if ($startIndex !== null or $startSectionIndex === null) {
            $startSectionIndex = $startIndex;
          }

          if ($endSectionIndex == null and $startSectionIndex != null) {
            $endSectionIndex = $startSectionIndex;
          }

          if ($startSectionIndex == null and $endSectionIndex != null) {
            $startSectionIndex = $endSectionIndex;
          }

          //echo __LINE__ . "*** | startSectionIndex:" . $startSectionIndex . "\n";
          //echo __LINE__ . "*** | endSectionIndex  :" . $endSectionIndex . "\n";

        }

        //echo __LINE__ . "*** | got startSectionIndex:" . $startSectionIndex . "\n";
        //echo __LINE__ . "*** | got endSectionIndex:" . $endSectionIndex . "\n";

        if ($startSectionIndex !== null and $endSectionIndex !== null) {
          //echo __LINE__ . "*** | " . "" . "\n";
          //echo __LINE__ . "*** | start index: " . $startSectionIndex . "\n";
          //echo __LINE__ . "*** | end index: " . $endSectionIndex . "\n";


          //echo __LINE__ . "*** | start extract from : " . $startSectionIndex . "\n";
          //echo __LINE__ . "*** | size: " . ($endSectionIndex - $startSectionIndex + 1) . "\n";

          // 3=3 =>1
          // 3=4 => 2

          // 2=2 => 1
          // 2=3 => 2
          // 2=4 => 3

          $rangeList[] = array(
            $startSectionIndex, ($endSectionIndex - $startSectionIndex + 1)
          );

          //$items = $collection->extractItems($startSectionIndex, ($endSectionIndex - $startSectionIndex + 1));
          //$block->append($items);

        }

        $nextTokenIndexForCheck++;
      }

      return $rangeList;
    }

    public function extractInRange(\Funivan\PhpTokenizer\Collection $collection, $range) {

      $items = $collection->extractItems($range->from, $range->to);

      foreach ($items as $token) {

      }
    }

  }