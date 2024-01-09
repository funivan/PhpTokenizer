<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Query;

use Funivan\PhpTokenizer\Exception\Exception;
use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
use Funivan\PhpTokenizer\Token;

class Query implements QueryInterface
{
    /**
     * Array of check functions
     * As first argument accept token
     * Return boolean
     *
     * @var callable[]
     */
    protected $checkFunctions = [];

    /**
     * Storage of type conditions
     *
     * @var array
     */
    protected $type = [];

    /**
     * Storage of conditions conditions
     *
     * @var array
     */
    protected $value = [];

    /**
     * Storage of line conditions
     *
     * @var array
     */
    protected $line = [];

    /**
     * Storage of index conditions
     *
     * @var array
     */
    protected $index = [];

    /**
     * @return static
     */
    public static function create()
    {
        return new static();
    }

    /**
     * @param int|array $type Array<Int>|Int
     * @return $this
     */
    public function typeIs($type)
    {
        $types = $this->prepareIntValues($type);

        $this->checkFunctions[] = fn (Token $token) => in_array($token->getType(), $types, true);

        return $this;
    }

    /**
     * @param array|int $type Array<Int>|Int
     * @return $this
     */
    public function typeNot($type)
    {
        $types = $this->prepareIntValues($type);

        $this->checkFunctions[] = fn (Token $token) => ! in_array($token->getType(), $types, true);

        return $this;
    }

    /**
     * @param array|string $value Array<String>|String
     * @return $this
     */
    public function valueIs($value)
    {
        $values = $this->prepareValues($value);

        $this->checkFunctions[] = fn (Token $token) => in_array($token->getValue(), $values, true);

        return $this;
    }

    /**
     * @param array|string $value Array<String>|String
     * @return $this
     */
    public function valueNot($value)
    {
        $values = $this->prepareValues($value);

        $this->checkFunctions[] = fn (Token $token) => ! in_array($token->getValue(), $values, true);

        return $this;
    }

    /**
     * @param string[]|string $regex string[]
     * @return $this
     */
    public function valueLike($regex)
    {
        $regexConditions = $this->prepareValues($regex);

        $this->checkFunctions[] = function (Token $token) use ($regexConditions) {
            if (empty($regexConditions)) {
                return false;
            }

            $value = $token->getValue();

            if ($value === null) {
                return false;
            }
            foreach ($regexConditions as $regex) {
                if (! preg_match($regex, $value)) {
                    return false;
                }
            }

            return true;
        };

        return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexIs($index)
    {
        $indexNumbers = $this->prepareIntValues($index);

        $this->checkFunctions[] = fn (Token $token) => in_array($token->getIndex(), $indexNumbers, true);

        return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexNot($index)
    {
        $indexNumbers = $this->prepareIntValues($index);

        $this->checkFunctions[] = fn (Token $token) => ! in_array($token->getIndex(), $indexNumbers, true);

        return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexGt($index)
    {
        $indexNumbers = $this->prepareIntValues($index);

        $this->checkFunctions[] = fn (Token $token) => $token->getIndex() > max($indexNumbers);

        return $this;
    }

    /**
     * @param int|int[] $index
     * @return $this
     */
    public function indexLt($index)
    {
        $indexNumbers = $this->prepareIntValues($index);

        $this->checkFunctions[] = fn (Token $token) => $token->getIndex() < min($indexNumbers);

        return $this;
    }

    public function isValid(Token $token)
    {
        foreach ($this->checkFunctions as $check) {
            $result = $check($token);
            if (! is_bool($result)) {
                throw new Exception('Check function should return boolean value. Given:' . gettype($result));
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string|int|array $value String|Int|Array<String>|Array<Int>
     * @return array Array<String>
     * @throws \Exception
     */
    protected function prepareValues($value)
    {
        if ($value === null) {
            return [];
        }

        if (is_object($value)) {
            throw new InvalidArgumentException('Invalid conditions. Must be string or array of string');
        }

        $value = array_values((array) $value);

        foreach ($value as $k => $val) {
            if (! is_string($val) and ! is_numeric($val)) {
                throw new InvalidArgumentException('Invalid conditions. Must be string');
            }

            $value[$k] = (string) $val;
        }
        return $value;
    }

    /**
     * @param array|int $value Array<Int>|Int
     * @return array
     * @throws \Exception
     */
    protected function prepareIntValues($value)
    {
        if ($value === null) {
            return [];
        }

        if (is_object($value)) {
            throw new InvalidArgumentException('Invalid condition value. Must be int. Object given');
        }

        $value = array_values((array) $value);

        foreach ($value as $intValue) {
            if (! is_int($intValue)) {
                throw new InvalidArgumentException('Invalid conditions. Must be integer. Given:' . gettype($intValue));
            }
        }

        return $value;
    }

    /**
     * Under development
     *
     * @return $this
     */
    public function custom(callable $checkFunction)
    {
        $this->checkFunctions[] = $checkFunction;
        return $this;
    }
}
