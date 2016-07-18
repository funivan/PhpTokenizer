# Parameters Pattern
you can use 'ParametersPattern' to easily extract parameters from the functions or methods.

Task: Print second parameter of the function
```php
  
  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Pattern\PatternMatcher;
  use Funivan\PhpTokenizer\Pattern\Patterns\ParametersPattern;

  $source = '<?php  
   function custom($data, $row){
   
   }
   
   function getName($id){
   
   }
   
   function getData($id, $default = [1,4,5]){
   
   }
  ';

  $collection = Collection::createFromString($source);

  $parameterPattern = new ParametersPattern();
  $parameterPattern->withArgument(2);
  $parameterPattern->outputArgument(2);


  $patternMatcher = (new PatternMatcher($collection))->apply($parameterPattern);
  $result = $patternMatcher->getCollections();
  foreach ($result as $collection){
    echo $collection->assemble();
    echo "\n";
  }
```