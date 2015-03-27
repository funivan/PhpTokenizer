<?

  namespace Funivan\PhpTokenizer\Extractor;

  use Funivan\PhpTokenizer\Query\QueryProcessor\QueryProcessorResult;

  class SectionRange {

    private $start = null;

    private $end = null;

    /**
     * @return null|int
     */
    public function getStart() {
      return $this->start;
    }

    /**
     * @param int $start
     * @return $this
     */
    public function setStart($start) {
      $this->start = $start;
      return $this;
    }

    /**
     * @return null|int
     */
    public function getEnd() {
      return $this->end;
    }

    /**
     * @param int $end
     * @return $this
     */
    public function setEnd($end) {
      $this->end = $end;
      return $this;
    }

    public function applyQueryProcessorResult(QueryProcessorResult $result) {

      $endIndex = $result->getEndIndex();

      if ($endIndex !== null) {
        if ($result->getEndIndexStrategy() === QueryProcessorResult::STRATEGY_FORCE) {
          $this->end = $endIndex;
        } else {
          # soft strategy
          if ($endIndex > $this->end) {
            $this->end = $endIndex;
          }
        }
      }


      $startIndex = $result->getStartIndex();

      if ($startIndex !== null) {
        if ($result->getStartIndex() === QueryProcessorResult::STRATEGY_FORCE) {
          $this->start = $startIndex;
        } else {
          # soft strategy
          if ($startIndex < $this->end) {
            $this->start = $startIndex;
          }
        }
      }


      $this->recalculateIndexes();

    }

    /**
     * @return $this
     */
    public function reset() {
      $this->start = null;
      $this->end = null;
      return $this;
    }

    /**
     * Range is information about start and end
     * If we set end we should have start and vice versa
     */
    private function recalculateIndexes() {

      if ($this->start == null and $this->end !== null) {
        $this->start = $this->end;
      }

      if ($this->end == null and $this->start !== null) {
        $this->end = $this->start;
      }

    }

  }