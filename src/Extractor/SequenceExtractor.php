<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 3/11/15
   */
  /**
   *
   * @package Funivan\PhpTokenizer\Extractor
   */
  abstract class SequenceExtractor implements ExtractorInterface {

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var null|ExtractorInterface
     */
    protected $child = null;

    /**
     * @param null $childName
     */
    public function __construct($childName = null) {
      $this->name = $childName;
    }


    /**
     * @param string|null $name
     * @return static
     */
    public static function create($name = null) {
      return new static($name);
    }

    /**
     * @param ExtractorInterface $extractor
     * @return mixed
     */
    public function with(ExtractorInterface $extractor = null) {
      $this->child = $extractor;
      return $this;
    }


    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @return array
     */
    public function getRangeList(\Funivan\PhpTokenizer\Collection $collection) {
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

          //echo __LINE__ . "*** | endIndex: " . $endIndex . "\n";

          if ($endIndex !== null or $endSectionIndex == null) {
            $endSectionIndex = $endIndex;
          }

          $startIndex = $result->getStartIndex();
          //echo __LINE__ . "*** | startIndex: " . $startIndex . "\n";
          if ($startIndex !== null or $startSectionIndex === null) {
            $startSectionIndex = $startIndex;
          }

          if ($endSectionIndex == null and $startSectionIndex !== null) {
            $endSectionIndex = $startSectionIndex;
          }

          if ($startSectionIndex == null and $endSectionIndex !== null) {
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

          $rangeList[] = new ExtractorResult($startSectionIndex, $endSectionIndex);


          //$items = $collection->extractItems($startSectionIndex, ($endSectionIndex - $startSectionIndex + 1));
          //$block->append($items);

        }

        $nextTokenIndexForCheck++;
      }

      return $rangeList;
    }

    /**
     * @return ExtractorInterface|null
     */
    public function getChild() {
      return $this->child;
    }

    /**
     * @return string|null
     */
    public function getName() {
      return $this->name;
    }

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param null $name
     * @return \Funivan\PhpTokenizer\Block
     */
    public function extract(\Funivan\PhpTokenizer\Collection $collection, $name = null) {
      $extractor = new Extractor($collection, $this);
      return $extractor->fetchBlocks($name);
    }

  }