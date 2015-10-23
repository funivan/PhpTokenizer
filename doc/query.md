# Query
You can validate token by simple `if` `else` keywords. But it is move preferable to use `Query` class.
`Query` contain conditions and check if token is valid to all of this conditions.


Lets see simple example:


```
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

# Common used conditions

Our `Query` check token by type, value, index and etc. Here is several most used conditions:
- `typeIs` check if type is equal
- `typeNot` check if type is not equal
- `valueIs` check if value is equal
- `valueLike` check value by regexp
- `valueNot` check if value is not equal
- `custom` check token by custom callback


# Check token by custom callback

```php
  use Funivan\PhpTokenizer\Query\Query;
  use Funivan\PhpTokenizer\Token;

  $q = new Query();
  $q->custom(function (Token $token) {
    return ($token->getValue() === '$a' and $token->getLine() > 100);
  });
```

# Where i will use it?
 
- You can extract tokens from collection with `Query`

```php

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
You will find all `$a` variables

- Find/modify sequence of tokens. This is complicated example and you can read more in next sections.
 
```php
 use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;
  use Funivan\PhpTokenizer\Strategy\Strict;

  $source = '<?php  
   header("Location : /");
  ';


  $collection = Collection::createFromString($source);

  (new Pattern($collection))->apply(function (QuerySequence $qs) {
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

```
New code will be:
```php
<?php  
   $this->redirect("/");
```


[Index](index.md)
