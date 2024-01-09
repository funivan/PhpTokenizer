<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Pattern\Patterns;

use Exception;
use Funivan\PhpTokenizer\Collection;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
use Funivan\PhpTokenizer\Strategy\Section;
use Funivan\PhpTokenizer\Token;

class ParametersPattern implements PatternInterface
{
    /**
     * @var array
     */
    private $argumentCheck = [];

    /**
     * @var int|null
     */
    private $outputArgument = null;

    /**
     * @var bool|null
     */
    private $outputPreparedArgument = null;

    public function __construct()
    {
        $this->outputFull();
    }

    /**
     * @return Collection|null
     * @throws Exception
     */
    public function __invoke(QuerySequence $querySequence)
    {
        $section = $querySequence->section('(', ')');
        if ($section->count() === 0) {
            return null;
        }

        $section->slice(1, -1);

        if (empty($this->argumentCheck) and $this->outputArgument === null) {
            return $section;
        }

        /** @var Collection[] $arguments */
        $arguments = $this->getArguments($section);

        foreach ($this->argumentCheck as $index => $check) {
            $argumentTokens = $arguments[$index] ?? new Collection();
            $result = $check($argumentTokens);

            if (! is_bool($result)) {
                throw new Exception('Argument check function should return boolean');
            }

            if ($result === false) {
                return null;
            }
        }

        if ($this->outputArgument !== null) {
            $argumentCollection = ! empty($arguments[$this->outputArgument]) ? $arguments[$this->outputArgument] : null;

            # Do not remove T_WHITESPACE tokens from the argument output
            if ($this->outputPreparedArgument === false) {
                return $argumentCollection;
            }

            // trim first and last whitespace token
            $first = $argumentCollection->getFirst();
            $last = $argumentCollection->getLast();

            $from = 0;
            $to = null;
            if ($first !== null and $first->getType() === T_WHITESPACE) {
                $from = 1;
            }
            if ($last !== null and $last->getType() === T_WHITESPACE) {
                $to = -1;
            }

            return $argumentCollection->extractItems($from, $to);
        }

        # output full
        return $section;
    }

    /**
     * @return $this
     */
    public function withArgument(int $index, callable $check = null): self
    {
        if ($check === null) {
            $check = fn (Collection $argumentTokens) => $argumentTokens->count() !== 0;
        }
        $this->argumentCheck[$index] = $check;
        return $this;
    }

    /**
     * @return $this
     */
    public function withoutArgument(int $index): self
    {
        $check = fn (Collection $argumentTokens) => $argumentTokens->count() === 0;

        $this->argumentCheck[$index] = $check;
        return $this;
    }

    /**
     * @return Collection[]
     */
    protected function getArguments(Collection $section): array
    {
        /** @var Token $skipToToken */
        $skipToToken = null;
        $argumentIndex = 1;
        $arguments = [];
        $tokensNum = ($section->count() - 1);
        for ($tokenIndex = 0; $tokenIndex <= $tokensNum; $tokenIndex++) {
            $token = $section->offsetGet($tokenIndex);

            if ($token === null) {
                return [];
            }

            if ($skipToToken === null or $token->getIndex() >= $skipToToken->getIndex()) {
                if ($token->getValue() === ',') {
                    $argumentIndex++;
                    continue;
                }
                $skipToToken = $this->getEndArray($token, $section, $tokenIndex);
            }

            if (! isset($arguments[$argumentIndex])) {
                $arguments[$argumentIndex] = new Collection();
            }
            $arguments[$argumentIndex][] = $token;
        }

        return $arguments;
    }

    /**
     * @return Token
     */
    private function getEndArray(Token $token, Collection $section, int $index)
    {
        # check if we have array start

        if ($token->getValue() === '[') {
            $result = (new Section())->setDelimiters('[', ']')->process($section, $index);
            return $result->getToken();
        }

        if ($token->getValue() === '(') {
            $result = (new Section())->setDelimiters('(', ')')->process($section, $index);
            return $result->getToken();
        }

        return null;
    }

    /**
     * @return $this
     */
    public function outputFull(): self
    {
        $this->outputArgument = null;
        $this->outputPreparedArgument = null;
        return $this;
    }

    /**
     * @param bool $prepared
     * @return $this
     */
    public function outputArgument(int $int, $prepared = true): self
    {
        $this->outputArgument = $int;
        $this->outputPreparedArgument = $prepared;
        return $this;
    }
}
