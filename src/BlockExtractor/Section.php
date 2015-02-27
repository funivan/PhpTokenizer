<?

  namespace Funivan\PhpTokenizer\BlockExtractor;

  use Funivan\PhpTokenizer\Query;

  class Section extends ExtractProcessor {

    /**
     * @var Query
     */
    protected $startQuery = null;

    /**
     * @var Query
     */
    protected $endQuery = null;

    /**
     * @param Query $startSection
     * @param Query $endSection
     */
    public function __construct(Query $startSection, Query $endSection) {
      $this->startQuery = $startSection;
      $this->endQuery = $endSection;
    }

    /**
     * @inheritdoc
     */
    public function process(\Funivan\PhpTokenizer\Collection $collection, $currentIndex) {

      $token = $collection->offsetGet($currentIndex);

      if (empty($token)) {
        return;
      }

      $blockEndFlag = null;

      foreach ($collection as $tokenIndex => $token) {

        if ($tokenIndex < $currentIndex) {
          continue;
        }

        if ($this->startQuery->isValid($token)) {
          $blockEndFlag++;
          if ($blockEndFlag == 1) {
            $this->startIndex = $tokenIndex;
          }

        } elseif ($this->endQuery->isValid($token)) {
          $blockEndFlag--;
        }

        if ($blockEndFlag === 0) {
          $this->endIndex = $tokenIndex;
          return;
        }
      }

      $this->startIndex = null;
    }

    public function getStartIndex() {
      return $this->startIndex;
    }

    public function getEndIndex() {
      return $this->endIndex;
    }

  }