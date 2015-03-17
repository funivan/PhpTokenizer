<?

  namespace Funivan\PhpTokenizer\Query\QueryProcessor;

  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Query\QueryInterface;

  class Strict implements QueryProcessorInterface {


    /**
     * @var \Funivan\PhpTokenizer\Query\Query
     */
    protected $query = null;

    /**
     * @param QueryInterface $query
     */
    public function __construct(QueryInterface $query) {
      $this->query = $query;
    }

    /**
     * @return Query
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

      if (empty($token)) {
        return $result;
      }

      $valid = $this->getQuery()->isValid($token);

      if (!$valid) {
        return $result;
      }

      $result->setEndIndex($currentIndex);
      $result->setNextTokenIndexForCheck($currentIndex + 1);

      return $result;
    }

  }