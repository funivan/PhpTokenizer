# Token


Your php code consists of words and symbols. Php processor can split your code to tokens.

`Token` class is a wrapper around standard token representation. 
This class contains:
1. [Type](#Token-type)
2. [Value](#Token-value)
3. [Line](#Token-line)
4. [Index](#Token-index)


## Token type
From official php manual.
> Various parts of the PHP language are represented internally by types like T_SR. 
> PHP outputs identifiers like this one in parse errors, like 
> "Parse error: unexpected T_SR, expecting ',' or ';' in script.php on line 10."

You can read more about token at [List of Parsed Tokens](http://php.net/manual/en/tokens.php)

So token type identifies what is the nature of the symbol.
Let's see some common types of tokens. 
```php
$tokenTypes = [
  T_CLASS       => 'class',
  T_ECHO        => 'echo',
  T_STRING_CAST => '(string)',
  T_VARIABLE    => '$foo',
];
```
For example we want to find all variables in our code. We do not know the variable name. 
But we know the type of words that we search: `T_VARIABLE`.
 
```php

  use Funivan\PhpTokenizer\Collection;

  $source = '<?php 
   $a = 1;
   echo $a;
  ';

  $collection = Collection::createFromString($source);

  foreach ($collection as $token) {

    if ($token->getType() === T_VARIABLE) {
      echo $token . "\n";
    }

  }

```

You will see `$a` two times in your screen.
    
# Token value
You can get value from token by `getValue`. As we know our code consists of tokens. 
If we tokenize our code we get array of `Token`.
To get our code back we just need to concat all token values.   

Let's make a simple hack. Change all variables `$a` to `$b`.
```php

  use Funivan\PhpTokenizer\Collection;

  $source = '<?php 
   $a = 1;
   echo $a;
  ';

  $newCode = ''; 
  $collection = Collection::createFromString($source);

  foreach ($collection as $token) {

    if ($token->getValue() === '$a') {
      $token->setValue('$b');
    }
    
    $newCode = $newCode . $token->getValue();
  }
  
  echo $newCode;

```

Now your `$newCode` is
```php
<?php 
   $b = 1;
   echo $b;
```

#Token line
It is useful when we want to find tokens in a specific line. For example, remove empty string in line `34`.

# Token index
Token index is my custom feature. This index inited when we create tokens from the string.
```

  use Funivan\PhpTokenizer\Collection;

  $source = '<?php 
   $a = 1;
   echo $a;
  ';

   
  $collection = Collection::createFromString($source);
  $token = $collection->getFirst();

  echo $token->getIndex();

```
Tou should see `0`.
In most cases you do not need it. This index is used in the internal part of the library (when we need to extract tokens by start and end tokens).

[Index](index.md)
