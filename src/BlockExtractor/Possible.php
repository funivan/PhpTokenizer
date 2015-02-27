<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  class Possible extends ExtractProcessor {

    /**
     * @var \Funivan\PhpTokenizer\Query
     */
    protected $query = null;

    public function __construct($query) {
      $this->query = $query;
    }


    /**
     * @return \Funivan\PhpTokenizer\Query
     */
    public function getQuery() {
      return $this->query;
    }

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $token = $collection[$currentIndex];

      if (empty($token)) {
        return;
      }

      $isValid = $this->getQuery()->isValid($token);

      if ($isValid) {
        $this->startIndex = $currentIndex;
        $this->endIndex = $currentIndex;
      }

    }

  }