<?

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Query\QueryInterface;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\QueryProcessor
   */
  class Section implements QueryProcessorInterface {

    /**
     * @var QueryInterface
     */
    protected $startQuery = null;

    /**
     * @var QueryInterface
     */
    protected $endQuery = null;

    /**
     * @param QueryInterface $startSection
     * @param QueryInterface $endSection
     */
    public function __construct(QueryInterface $startSection, QueryInterface $endSection) {
      $this->startQuery = $startSection;
      $this->endQuery = $endSection;
    }

    /**
     * @param \Funivan\PhpTokenizer\Collection $collection
     * @param int $currentIndex
     * @return QueryProcessorResult
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {
      $result = new QueryProcessorResult();

      $token = $collection->offsetGet($currentIndex);
      if (empty($token) or $this->startQuery->isValid($token) == false) {
        return $result;
      }

      $blockEndFlag = null;
      $startIndex = null;

      foreach ($collection as $tokenIndex => $token) {

        if ($tokenIndex < $currentIndex) {
          continue;
        }

        if ($this->startQuery->isValid($token)) {
          $blockEndFlag++;
          if ($blockEndFlag == 1) {
            $startIndex = $tokenIndex;
          }

        } elseif ($startIndex !== null and $this->endQuery->isValid($token)) {
          $blockEndFlag--;
        }

        if ($blockEndFlag === 0) {
          $endIndex = $tokenIndex;
          break;
        }
      }

      if (isset($startIndex) and isset($endIndex)) {
        $result->moveEndIndex($endIndex);
        $result->setNextTokenIndexForCheck(++$endIndex);
      }

      return $result;
    }

  }