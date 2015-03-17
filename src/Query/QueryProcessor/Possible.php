<?

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Query\QueryInterface;

  /**
   *
   * @package Funivan\PhpTokenizer\Query\QueryProcessor
   */
  class Possible implements QueryProcessorInterface {

    /**
     * @var QueryInterface
     */
    protected $query = null;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query) {
      $this->query = $query;
    }


    /**
     * @return \Funivan\PhpTokenizer\Query\Query
     */
    public function getQuery() {
      return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $token = $collection->offsetGet($currentIndex);

      $result = new \Funivan\PhpTokenizer\BlockExtractor\ExtractorResult();

      $result->setNextTokenIndexForCheck($currentIndex);

      if (empty($token)) {
        return $result;
      }

      $isValid = $this->getQuery()->isValid($token);

      if (!$isValid) {
        return $result;
      }

      $result->setEndIndex($currentIndex);
      $result->setNextTokenIndexForCheck($currentIndex + 1);

      return $result;
    }

  }