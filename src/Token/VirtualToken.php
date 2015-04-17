<?
  namespace Funivan\PhpTokenizer\Token;

  /**
   * This class used for NullAble pattern
   *
   * @package Funivan\PhpTokenizer
   */
  class VirtualToken extends \Funivan\PhpTokenizer\Token {

    /**
     *
     * @param array $data
     * @throws \Exception
     */
    public function __construct(array $data = []) {
      if (!empty($data)) {
        throw new \Funivan\PhpTokenizer\Exception("You cant create dummy with any data");
      }

      $this->line = static::INVALID_LINE;
      $this->value = static::INVALID_VALUE;
      $this->type = static::INVALID_TYPE;
      $this->index = static::INVALID_INDEX;
    }

    /**
     * Create new Dummy token
     */
    public static function create() {
      return new static();
    }

    /**
     * @internal
     * @param array $data
     * @return $this|void
     * @throws \Funivan\PhpTokenizer\Exception
     */
    protected function setData(array $data) {
      throw new \Funivan\PhpTokenizer\Exception('This token is virtual and cant be changed');
    }

    /**
     * @internal
     * @param $type
     * @return $this|void
     * @throws \Funivan\PhpTokenizer\Exception
     */
    public function setType($type) {
      throw new \Funivan\PhpTokenizer\Exception('This token is virtual and cant be changed');
    }

    /**
     * @internal
     * @param int|string $value
     * @return $this|void
     * @throws \Funivan\PhpTokenizer\Exception
     */
    public function setValue($value) {
      throw new \Funivan\PhpTokenizer\Exception('This token is virtual and cant be changed');
    }

    /**
     * @internal
     * @param int $line
     * @return $this|void
     * @throws \Funivan\PhpTokenizer\Exception
     */
    public function setLine($line) {
      throw new \Funivan\PhpTokenizer\Exception('This token is virtual and cant be changed');
    }

    /**
     * @internal
     * @param null $index
     * @return $this|void
     * @throws \Funivan\PhpTokenizer\Exception
     */
    public function setIndex($index) {
      throw new \Funivan\PhpTokenizer\Exception('This token is virtual and cant be changed');
    }

  }