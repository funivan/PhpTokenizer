# Php tokenizer upgrade guide

## from 0.1.1 to 0.1.2
  - Deprecated `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::nameIs` use `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::withName`  
  - Deprecated `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::whereName` use `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::withName`  
  
## from 0.1.0 to 0.1.1
This package is under development. Please use strict version in composer json
  - rename `\Funivan\PhpTokenizer\Strategy\BaseStrategy` to `\Funivan\PhpTokenizer\Strategy\QueryStrategy` 
  - replace `\Funivan\PhpTokenizer\Collection::getDumpString()` to `\Funivan\PhpTokenizer\Collection::dump($collection)` 
  - rename `\Funivan\PhpTokenizer\Collection::initFromString($code)` with `\Funivan\PhpTokenizer\Collection::createFromString($code)` 
  initFromString is still alive but since 0.1.1 it is deprecated