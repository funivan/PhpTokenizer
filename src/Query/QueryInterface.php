<?php

declare(strict_types=1);

namespace Funivan\PhpTokenizer\Query;

use Funivan\PhpTokenizer\Token;

interface QueryInterface
{
    /**
     * Check if token is valid for current query
     *
     * @return boolean
     */
    public function isValid(Token $token);
}
