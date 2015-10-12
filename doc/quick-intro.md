# Quick Intro 
This library helps us to develop better software and refactor our code efficiently.
When I need to modify hundreds lines of code and I can not use my IDE for this task, I use `PhpTokenizer` library. 
Here is few cases how I use this library:

1. Change `$this->response()` to `$this->getResponse()` in methods which name starts with `action` word.
2. Add `public` keyword to all methods that does not have visibility keyword.
3. Replace old and unused code.
4. Etc ... 


# How it workds
For example you have php code. You load your code to `Collection` using `Collection::createFromString($code)`
This function will create collection of tokens. You can already find and modify your code
```php

  require __DIR__ . '/vendor/autoload.php';

  use Funivan\PhpTokenizer\Collection;

  $source = '<?php 
  echo "Hello world"; 
  ';

  $collection = Collection::createFromString($source);
  
  foreach ($collection as $token) {
  
    if ($token->getType() === T_CONSTANT_ENCAPSED_STRING) {
      $value = substr($token->getValue(), 1, -1);
      $token->setValue("'" . $value . "'");
    }

  }

  echo (string) $collection;

```
With this code we change all strings in double quotes to single quotes. As you can see it is pretty simple. 
  
`Collection` is array of `Token` (tokens). What is it, token?
`Token` is the minimum unit of this library. This data structure consist of 3 values: 

1. Type
2. Value
3. Line
4. Index

Php build tokens from string using `token_get_all` built-in function.


If we have complicated condition it is easy to use `Query`
`Query` is condition holder class. Lets rewrite our example


```php


  require __DIR__ . '/vendor/autoload.php';

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Query\Query;

  $source = '<?php 
  echo "Hello world";
  ';

  $collection = Collection::createFromString($source);

  $query = new Query();
  $query->typeIs(T_CONSTANT_ENCAPSED_STRING);
  
  foreach ($collection as $token) {

    if ($query->isValid($token)) {
      $value = substr($token->getValue(), 1, -1);
      $token->setValue("'" . $value . "'");
    }

  }

  echo (string) $collection;

```
You can validate `Token` by several conditions. Take a look at `Query` class.


Traverse over collection is simple but it is not so easy to find code by multiple queries (conditions). 
For this task there are `Pattern` and `QuerySequence` classes

`QuerySequence` is sequence of several queries.
`Pattern` used to combine multiple `QuerySequence`

For example we want to change all `echo` statements to `$output->write()`
  
```php

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\Pattern;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;

  $source = '<?php 
  echo "Hello world"; 
  ';

  $collection = Collection::createFromString($source);

  (new Pattern($collection))->apply(function (QuerySequence $q) {
    $start = $q->strict('echo');
    $space = $q->possible(T_WHITESPACE);
    $end = $q->search(';');

    if ($q->isValid()) {
      $space->remove();
      $start->setValue('$output->write(');
      $end->prependToValue(')');
    }
  });

  echo (string) $collection;
  
```

[index](index.md)



