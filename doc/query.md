# Query
You can validate token by simple `if` `else` keywords. But it is more preferable to use `Query` class.
`Query` contains conditions and checks if token is valid to all of these conditions.


Let's see the simple example:

```php
  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Query\Query;

  $source = '<?php 
   $a = 1;
   $b = 2;
   echo $a;
   echo $b;
  ';


  $collection = Collection::createFromString($source);

  $q = new Query();
  $q->typeIs(T_VARIABLE);
  $q->valueIs('$a');


  foreach ($collection as $token) {
    if ($q->isValid($token)) {
      echo $token->getValue() . "\n";
    }
  }

```

# Commonly used conditions

Our `Query` checks token by type, value, index and etc. There are several most used conditions:
- `typeIs` checks if type is equal
- `typeNot` checks if type is not equal
- `valueIs` checks if value is equal
- `valueLike` checks value by regexp
- `valueNot` checks if value is not equal
- `custom` checks token with custom function


# Check token with the custom function

```php
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;

  $q = new Query();
  $q->custom(function (Token $token) {
    return ($token->getValue() === '$a' and $token->getLine() > 100);
  });
```

# Where will I use it?
 
- You can extract tokens from the collection with the help of `Query`

```php

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Query\Query;

  $source = '<?php 
   $a = 1;
   $b = 2;
   echo $a;
   echo $b;
  ';


  $collection = Collection::createFromString($source);

  $q = new Query();
  $q->valueIs('$a');

  $tokens = $collection->find($q);

  print_r($tokens);
  
```
You will find all `$a` variables.

- Find/modify the sequence of tokens. This is a complicated example and you can read more about it in the next sections.
 
```php
  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Strict;

  $source = '<?php  
   header("Location : /");
  ';


  $collection = Collection::createFromString($source);

  (new PatternMatcher($collection))->apply(function (QuerySequence $qs) {
    $regexp = '!^(.)\s*Location\s*:\s*(.+)$!';
    
    // Use strict query with valueIs condition
    $start = $qs->process(Strict::create()->valueIs('header'));
    $qs->process(Strict::create()->valueIs('('));

    // Check token by type and value 
    $location = $qs->process(
      Strict::create()
        ->typeIs(T_CONSTANT_ENCAPSED_STRING)
        ->valueLike($regexp)
    );
    $qs->process(Strict::create()->valueIs(')'));

    if ($qs->isValid()) {
      // Modify token 
      $start->setValue('$this->redirect');
      $location->setValue(preg_replace($regexp, '$1$2', $location->getValue()));
    }
  });


  echo $collection; 
  
  // New code will be:  
  // $this->redirect("/");

```


[Index](index.md)
