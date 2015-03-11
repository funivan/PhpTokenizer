<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  use Funivan\PhpTokenizer\Query;

  class Strict extends QueryProcessor {


    /**
     * @var Query
     */
    protected $query = null;

    /**
     * @param Query $query
     */
    public function __construct(Query $query) {
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

      if (empty($token)) {
        return;
      }

      $valid = $this->getQuery()->isValid($token);

      if ($valid) {
        $this->endIndex = $currentIndex;
      }

    }

    public function getNextTokenIndexForCheck() {

    }
    
    
  }