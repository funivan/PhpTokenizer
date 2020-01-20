<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Strategy;


use Funivan\PhpTokenizer\Collection;

/**
 *
 *
 */
class Possible extends QueryStrategy
{

    /**
     * @inheritdoc
     */
    public function process(Collection $collection, $currentIndex)
    {

        $result = new StrategyResult();
        $result->setValid(true);

        $token = $collection->offsetGet($currentIndex);

        if ($token and $this->isValid($token)) {
            $result->setToken($token);
            ++$currentIndex;
        }

        $result->setNexTokenIndex($currentIndex);

        return $result;
    }

}
