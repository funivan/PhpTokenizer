# PhpTokenizer

[![GitHub tag](https://img.shields.io/github/tag/funivan/PhpTokenizer.svg?style=flat-square)](https://github.com/funivan/PhpTokenizer/tags)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Build Status](https://img.shields.io/travis/funivan/PhpTokenizer/master.svg?style=flat-square)](https://travis-ci.org/funivan/PhpTokenizer)
[![Coverage Status](https://img.shields.io/scrutinizer/coverage/g/funivan/PhpTokenizer.svg?style=flat-square)](https://scrutinizer-ci.com/g/funivan/PhpTokenizer/code-structure)
[![Quality Score](https://img.shields.io/scrutinizer/g/funivan/PhpTokenizer.svg?style=flat-square)](https://scrutinizer-ci.com/g/funivan/PhpTokenizer)
[![Total Downloads](https://img.shields.io/packagist/dt/funivan/php-tokenizer.svg?style=flat-square)](https://packagist.org/packages/funivan/php-tokenizer)

Wrapper around token_get_all. Easy to extract and modify tokens

## Install

Via Composer

``` bash
composer require funivan/php-tokenizer
```

## Usage
Reformat our code like PhpStorm. Lets create rule: place single spaces after `while`
  
```php

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\QuerySequence\QuerySequence;


  $source = "<?php while(){}"; // while (){}
  
  $collection = Collection::createFromString($source);
  
  (new PatternMatcher($collection))->apply(function (QuerySequence $checker) {

    $while = $checker->strict('while');
    $space = $checker->possible(T_WHITESPACE);

    if ($checker->isValid()) {
      $space->remove();
      $while->appendToValue(" ");
    }

  });

  echo (string) $collection;
   


```

## Documentation
[Documentation](doc/index.md)

## Testing

``` bash
./vendor/bin/phpunit
```

## Contributing

Please see [CONTRIBUTING](https://github.com/funivan/PhpTokenizer/blob/master/CONTRIBUTING.md) for details.

## Credits

- [funivan](https://github.com/funivan)
- [All Contributors](https://github.com/funivan/PhpTokenizer/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
