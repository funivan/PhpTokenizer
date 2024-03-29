<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Pattern\Patterns;

use Funivan\PhpTokenizer\Pattern\PatternMatcher;
use Funivan\PhpTokenizer\Query\Query;
use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

class FunctionCallPattern implements PatternInterface
{
    final public const OUTPUT_FULL = 1;

    final public const OUTPUT_ARGUMENTS = 2;

    /**
     * @var Query|null
     */
    private $nameQuery;

    /**
     * @var ParametersPattern
     */
    private $parametersPattern;

    /**
     * @var int
     */
    private $outputType = self::OUTPUT_FULL;

    /**
     * @return $this
     */
    public function outputFull(): self
    {
        $this->outputType = self::OUTPUT_FULL;
        return $this;
    }

    /**
     * @return $this
     */
    public function outputArguments(): self
    {
        $this->outputType = self::OUTPUT_ARGUMENTS;
        return $this;
    }

    /**
     * @return $this
     */
    public function withName(Query $query): self
    {
        $this->nameQuery = $query;
        return $this;
    }

    /**
     * @return $this
     */
    public function withParameters(ParametersPattern $pattern): self
    {
        $this->parametersPattern = $pattern;
        return $this;
    }

    public function __invoke(QuerySequence $querySequence)
    {
        $name = $querySequence->strict(T_STRING);
        if ($this->nameQuery !== null and $this->nameQuery->isValid($name) === false) {
            return null;
        }

        $querySequence->possible(T_WHITESPACE);
        $arguments = $querySequence->section('(', ')');

        if (! $querySequence->isValid()) {
            return null;
        }

        $querySequence->moveToToken($name);
        $before = $querySequence->move(-1);
        if ($before->getType() === T_WHITESPACE) {
            $before = $querySequence->move(-1);
        }

        if (in_array($before->getValue(), ['::', 'function', '->'])) {
            return null;
        }

        if ($this->parametersPattern !== null) {
            $pattern = (new PatternMatcher($arguments))->apply($this->parametersPattern);
            if (count($pattern->getCollections()) === 0) {
                return null;
            }
        }

        $lastToken = $arguments->getLast();
        if ($lastToken === null) {
            return null;
        }

        if ($this->outputType === self::OUTPUT_ARGUMENTS) {
            return $arguments;
        }

        return $querySequence->getCollection()->extractByTokens($name, $lastToken);
    }
}
