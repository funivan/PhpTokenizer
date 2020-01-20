<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\QuerySequence;

use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\Exception\InvalidArgumentException;
use Funivan\PhpTokenizer\Strategy\Move;
use Funivan\PhpTokenizer\Strategy\Possible;
use Funivan\PhpTokenizer\Strategy\QueryStrategy;
use Funivan\PhpTokenizer\Strategy\Search;
use Funivan\PhpTokenizer\Strategy\Section;
use Funivan\PhpTokenizer\Strategy\StrategyInterface;
use Funivan\PhpTokenizer\Strategy\Strict;
use Funivan\PhpTokenizer\Token;

/**
 * Start from specific position and check token from this position according to strategies
 */
class QuerySequence implements QuerySequenceInterface
{

    /**
     * @var bool
     */
    private $valid = true;

    /**
     * @var int
     */
    private $position;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var
     */
    private $skipWhitespaces = false;


    /**
     * @inheritdoc
     */
    public function __construct(Collection $collection, $initialPosition = 0)
    {
        $this->collection = $collection;
        $this->position = $initialPosition;
    }


    /**
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }


    /**
     * @param int $position
     * @return QuerySequence
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }


    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }


    /**
     * Strict validation of condition
     *
     * @param int|string|Strict $condition
     * @return Token
     */
    public function strict($condition)
    {
        $query = $this->buildStrategyCondition($condition, Strict::create());
        return $this->process($query);
    }


    /**
     * Check if token possible valid for our condition
     *
     * @param int|string|Possible $condition
     * @return Token
     */
    public function possible($condition)
    {
        $query = $this->buildStrategyCondition($condition, Possible::create());
        return $this->process($query);
    }


    /**
     * @param string $start
     * @param string $end
     * @return Collection
     */
    public function section($start, $end)
    {

        $token = $this->strict($start);
        if (!$token->isValid()) {
            # cant find start position
            return new Collection();
        }

        $this->moveToToken($token);

        $section = new Section();
        $section->setDelimiters($start, $end);
        $lastToken = $this->process($section);
        if (!$lastToken->isValid()) {
            return new Collection();
        }

        return $this->collection->extractByTokens($token, $lastToken);
    }


    /**
     * By default we search forward
     *
     * @param int|string|Search $condition
     * @param null $direction
     * @return Token
     */
    public function search($condition, $direction = null)
    {
        $strategy = Search::create();
        if ($direction !== null) {
            $strategy->setDirection($direction);
        }
        $query = $this->buildStrategyCondition($condition, $strategy);
        return $this->process($query);
    }


    /**
     * Relative move
     * +10 move forward 10 tokens
     * -5 move backward 5 tokens
     *
     * @param int $steps
     * @return Token
     */
    public function move($steps)
    {
        return $this->process(Move::create($steps));
    }


    /**
     * Move to specific position
     *
     * @param Token $token
     * @return Token|null
     */
    public function moveToToken(Token $token)
    {

        if (!$token->isValid()) {
            $this->setValid(false);
            return new Token();
        }

        $tokenIndex = $token->getIndex();


        foreach ($this->collection as $index => $collectionToken) {
            if ($collectionToken->getIndex() === $tokenIndex) {
                $this->setPosition($index);
                return $collectionToken;
            }
        }

        $this->setValid(false);
        return new Token();
    }


    /**
     * Array may contain Int, String or any StrategyInterface object
     *
     * @param array $conditions
     * @return Collection
     */
    public function sequence(array $conditions)
    {
        $range = new Collection();
        foreach ($conditions as $value) {
            $range[] = $this->checkFromSequence($value);
        }

        return $range;
    }


    /**
     * @param string|int|StrategyInterface $value
     * @return Token
     */
    private function checkFromSequence($value)
    {
        if ($value instanceof StrategyInterface) {
            $query = $value;
        } else {
            $query = $this->buildStrategyCondition($value, Strict::create());
        }

        return $this->process($query);
    }


    /**
     * @inheritdoc
     */
    public function process(StrategyInterface $strategy)
    {

        if ($this->isValid() === false) {
            return new Token();
        }

        $result = $strategy->process($this->collection, $this->getPosition());

        if ($result->isValid() === false) {
            $this->setValid(false);
            return new Token();
        }

        $position = $result->getNexTokenIndex();
        $this->setPosition($position);

        $token = $result->getToken();
        if ($token === null) {
            $token = new Token();
        }

        if ($this->skipWhitespaces and isset($this->collection[$position]) and $this->collection[$position]->getType() === T_WHITESPACE) {
            # skip whitespaces in next check
            $this->setPosition($position + 1);
        }

        return $token;
    }


    /**
     *
     * @param StrategyInterface|string|int $value
     * @param QueryStrategy $defaultStrategy
     * @return QueryStrategy
     */
    private function buildStrategyCondition($value, QueryStrategy $defaultStrategy)
    {

        if ($value instanceof $defaultStrategy) {
            return $value;
        }

        $query = $defaultStrategy;

        if (is_string($value)) {
            $query->valueIs($value);
            return $query;
        }

        if ($value === null) {
            $query->valueIs([]);
            return $query;
        }

        if (is_int($value)) {
            $query->typeIs($value);
            return $query;
        }


        throw new InvalidArgumentException('Invalid token condition. Expect string or int or StrategyInterface');
    }


    /**
     * @param boolean $valid
     * @return $this
     */
    public function setValid($valid)
    {
        if (!is_bool($valid)) {
            throw new InvalidArgumentException('Invalid flag. Expect boolean. Given:' . gettype($valid));
        }
        $this->valid = $valid;
        return $this;
    }


    /**
     * @return Token
     */
    public function getToken()
    {
        $position = $this->getPosition();
        $token = $this->getCollection()->offsetGet($position);

        if ($token !== null) {
            return $token;
        }

        return new Token();
    }


    /**
     * Indicate state of all conditions
     *
     * @return bool
     */
    public function isValid()
    {
        return ($this->valid === true);
    }


    /**
     * @param boolean $skipWhitespaces
     * @return $this
     */
    public function setSkipWhitespaces($skipWhitespaces)
    {
        $this->skipWhitespaces = $skipWhitespaces;
        return $this;
    }

}
