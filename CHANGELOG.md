#Changelog
All Notable changes to `PhpTokenizer` will be documented in this file
## Planned changes
  ### Changed
    - BC. `Collection::offsetSet` now returns `void` instead of its previous return type `this`

## 0.3.0 - [2019-01-20]
  ### Changed
    - Update to php 7.2

## 0.2.0 - [2018-06-11]
  ### Removed
    - Remove method `Collection::initFromString` use `Collection::createFromString` 
    - Remove method `Collection::getItems` use `Collection::getTokens`
    - Remove method `Collection::map` use `Collection::each` 
    - Remove class `Pattern` use PatternMatcher
    - Remove method `ClassPattern::nameIs` use `ClassPattern::withName`
    - Remove method `ClassPattern::whereName` use `ClassPattern::withName`
    
## 0.1.3 - [2017-07-24]
  ### Fixed
    - Use flag TOKEN_PARSE to parse tokens correctly
  ### Changed
    - #9 By default output full class instead of body
  ### Added 
    - #3 add new method `Token::equal`

## 0.1.2 - [2017-03-20]
  ### Removed 
    - `fiv/collection` package
  ### Deprecated 
    - `Collection::map` use `Collection::each` 
    - `Collection::getItems` use `Collection::getToken` 

## 0.1.2-alpha5 - 2016-07-18
 ### Deprecated
   - `Funivan\PhpTokenizer\Pattern\Pattern` use `Funivan\PhpTokenizer\Pattern\PatternMatcher`
   - `ClassPattern::nameIs`
   - `ClassPattern::whereName`
 ### Fixed
   - `Funivan\PhpTokenizer\Helper::getTokensFromString`. Better detection of token line
   - `Funivan\PhpTokenizer\extractByTokens` We detect token range by token global index. See documentation
 ### Changed
  - `Funivan\PhpTokenizer\Strategy\Section` Strict detection of section. Next token after current must be section start token 
 ### Added 
   - `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern` `withDocComment`, `withoutDocComment`, `withPossibleDocComment`
   - `Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern` `withModifier`, `withoutModifier`, `withAnyModifier`
   - `Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern`

## 0.1.1 - 2015-07-28
  ### Removed
    - `\Funivan\PhpTokenizer\Strategy\BaseStrategy` 
    - `\Funivan\PhpTokenizer\Collection::getDumpString`
  ### Deprecated 
    - `\Funivan\PhpTokenizer\Collection::initFromString`
  ### Fixed
    - `\Funivan\PhpTokenizer\Strategy\Search::process`
  ### Added 
    - `\Funivan\PhpTokenizer\Strategy\QueryStrategy` 
    - `\Funivan\PhpTokenizer\Pattern\Patterns\ClassPattern::whereName` 
    - `Funivan\PhpTokenizer\Helper::dump`
    - `\Funivan\PhpTokenizer\Helper::dump`
