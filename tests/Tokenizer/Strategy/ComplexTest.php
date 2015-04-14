<?

  namespace Test\Funivan\PhpTokenizer\Tokenizer\Strategy;

  use Funivan\PhpTokenizer\Collection;
  use Funivan\PhpTokenizer\Finder;
  use Funivan\PhpTokenizer\Strategy\Possible;

  class ComplexTest extends \PHPUnit_Framework_TestCase {

    public function testComplex() {
      $code = '<? 
      
      if(!is_array($variable)) {
        $variable = (array) $variable;
      }
      
      ';

      $finder = new Finder(Collection::initFromString($code));

      while ($q = $finder->iterate()) {


        $q->valueIs('if');
        $q->valueIs('(');
        $q->check(Possible::create()->valueIs('!'));
        $q->valueIs('is_array');
        $q->valueIs('(');
        $q->search('{');


        $condition = $q->section('(', ')');

        $endSection = $q->section('{', '}');


        //$token = $q->check(Strict::create()->valueIs('if'));
        //$q->check(Section::create()->setDelimiters('(', ')'));


      }

    }

  }
