<?

  namespace Funivan\PhpTokenizer\Pattern\Patterns;

  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  /**
   *
   */
  class FunctionCallPattern implements PatternInterface {


    /**
     * @inheritdoc
     */
    public function __invoke(QuerySequence $querySequence) {

      $name = $querySequence->strict(T_STRING);
      $querySequence->possible(T_WHITESPACE);
      $arguments = $querySequence->section('(', ')');

      if (!$querySequence->isValid()) {
        return null;
      }

      $querySequence->moveToToken($name);
      $before = $querySequence->move(-1);
      if ($before->getType() === T_WHITESPACE) {
        $before = $querySequence->move(-1);
      }

      if (in_array($before->getValue(), ['::', 'function'])) {
        return null;
      }

      return $querySequence->getCollection()->extractByTokens($name, $arguments->getLast());
    }

  }