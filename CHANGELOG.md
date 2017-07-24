#Changelog
All Notable changes to `PhpTokenizer` will be documented in this file
## 0.1.3 - [unreleased]
 - fixed Use flag TOKEN_PARSE to parse tokens correctly

## 0.1.2 - [2017-03-20] 
  - remove `fiv/collection` package
  - deprecated `Collection::map` use `Collection::each` 
  - deprecated `Collection::getItems` use `Collection::getToken` 

 
## 0.1.2-alpha5 - 2016-07-18
 - deprecated `Funivan\PhpTokenizer\Pattern\Pattern` use `Funivan\PhpTokenizer\Pattern\PatternMatcher`
 - deprecated `ClassPattern::nameIs`
 - deprecated `ClassPattern::whereName`
 - fixed  `Funivan\PhpTokenizer\Helper::getTokensFromString`. Better detection of token line
 - fixed `Funivan\PhpTokenizer\extractByTokens` We detect token range by token global index. See documentation
 - changed `Funivan\PhpTokenizer\Strategy\Section` Strict detection of section. Next token after current must be section start token 
 - added `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern` `withDocComment`, `withoutDocComment`, `withPossibleDocComment`
 - added `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern` `withModifier`, `withoutModifier`, `withAnyModifier`
 - added `Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern`

## 0.1.1 - 2015-07-28
  - fixed `\Funivan\PhpTokenizer\Strategy\Search::process`
  - deprecated `\Funivan\PhpTokenizer\Collection::initFromString`
  - added `\Funivan\PhpTokenizer\Strategy\QueryStrategy` 
  - removed `\Funivan\PhpTokenizer\Strategy\BaseStrategy` 
  - added `\Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::whereName` 
  - added `Funivan\PhpTokenizer\Helper::dump`
  - removed `\Funivan\PhpTokenizer\Collection::getDumpString` 
  - added `\Funivan\PhpTokenizer\Helper::dump`