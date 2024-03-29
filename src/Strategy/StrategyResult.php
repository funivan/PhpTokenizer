<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Strategy;

use Funivan\PhpTokenizer\Token;

class StrategyResult
{
    /**
     * @var Token
     */
    private $token = null;

    /**
     * @var int|null
     */
    private $nexTokenIndex = null;

    /**
     * @var bool
     */
    private $valid = false;

    /**
     * @return Token|null
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return boolean
     */
    public function isValid()
    {
        return ($this->valid === true);
    }

    /**
     * @param boolean $valid
     * @return $this
     */
    public function setValid($valid)
    {
        $this->valid = (bool) $valid;
        return $this;
    }

    /**
     * @return $this
     */
    public function setToken(Token $token)
    {
        $this->token = $token;
        return $this;
    }

    /**
     * @param int $nexTokenIndex
     * @return $this
     */
    public function setNexTokenIndex($nexTokenIndex)
    {
        $this->nexTokenIndex = $nexTokenIndex;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getNexTokenIndex()
    {
        return $this->nexTokenIndex;
    }
}
