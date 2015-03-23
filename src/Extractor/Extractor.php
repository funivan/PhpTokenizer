<?php

  namespace Funivan\PhpTokenizer\Extractor;

  /**
   *
   * @todo rename class
   *
   * @package Funivan\PhpTokenizer\Extractor
   */
  use Funivan\PhpTokenizer\Block;

  /**
   *
   * @package Funivan\PhpTokenizer\Extractor
   */
  class Extractor {

    /**
     * @var \Funivan\PhpTokenizer\Collection
     */
    protected $collection;

    /**
     * @var ExtractorInterface
     */
    private $extractor;

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param ExtractorInterface $extractor
     */
    public function __construct(\Funivan\PhpTokenizer\Collection $collection, ExtractorInterface $extractor) {
      $this->collection = $collection;
      $this->extractor = $extractor;
    }

    /**
     * @param null $name
     * @return Block
     */
    public function fetchBlocks($name = null) {


      $ranges = $this->getRangeRecursively($this->extractor, new ExtractorResult(0, $this->collection->count() - 1));

      # remove ranges with empty child
      $ranges = $this->cleanRanges($ranges);

      $blocks = new Block();
      $blocks = $this->getBlocksFromRanges($blocks, $ranges, $name);

      return $blocks;
    }

    /**
     * @param ExtractorInterface $extractor
     * @param ExtractorResult $range
     * @return array
     */
    private function getRangeRecursively(ExtractorInterface $extractor, ExtractorResult $range) {

      $collectionForSearch = $this->collection->extractItems($range->getStart(), $range->getLength());
      $ranges = $extractor->getRangeList($collectionForSearch);
      $result = array();
      foreach ($ranges as $index => $rangeResult) {


        $rangeResult = new ExtractorResult(
          $rangeResult->getStart() + $range->getStart(),
          $rangeResult->getEnd() + $range->getStart()
        );


        $result[$index]['name'] = $extractor->getName();
        $result[$index]['range'] = $rangeResult;
        $child = $extractor->getChild();
        if ($child) {
          $childResult = $this->getRangeRecursively($extractor->getChild(), $rangeResult);
          $result[$index]['child'] = $childResult;

        }

      }

      return $result;
    }

    /**
     * @param array $ranges
     * @return array
     */
    private function cleanRanges($ranges) {
      foreach ($ranges as $index => $range) {

        if (!isset($range['child'])) {
          // last element
          continue;
        }

        if (!empty($range['child'])) {
          $range['child'] = $this->cleanRanges($range['child']);
        }

        if (empty($range['child'])) {
          unset($ranges[$index]);
        }

      }

      return array_values($ranges);
    }

    /**
     * @param Block $block
     * @param array $ranges
     * @param null $name
     * @return Block
     */
    private function getBlocksFromRanges(Block $block, $ranges, $name = null) {


      foreach ($ranges as $data) {

        if ($data['name'] !== $name and $name !== null) {
          if (!empty($data['child'])) {
            $this->getBlocksFromRanges($block, $data['child'], $name);
          }
          continue;
        }

        /** @var ExtractorResult $range */
        $range = $data['range'];
        $items = $this->collection->extractItems($range->getStart(), $range->getLength());
        $block->append($items);
      }

      return $block;
    }

  }