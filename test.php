<?php


  use Funivan\PhpTokenizer\Finder;

  require 'vendor/autoload.php';



  // Query
  // Simple check if token is valid according to current conditions

  // strategy or PROCESSOR
  // 1. Strict = stop find and return false
  //     - with direction - checkForward / checkBack
  // 2. Move = move to particular token forward or back
  // 3. Search = iterate next and next while we will find token
  //      - with direction - searchForward / searchBack 
  // 4. Possible = if valid ok. else - include token in next check
  //      - with direction - possibleForward / possibleBack
  //
  //      


  class PossibleWhitespace {

  }

  $code = '<?
  
  $table->fetchAll(
    $table->select()
  );
  
  $db-> fetchAll($db->select());
  $dd-> fetchAll($rf->select());
  
  
  ';


  $finder = Finder::initFromString($code);

  /** @var \Funivan\PhpTokenizer\TokenStreamProcess $q */
  while ($q = $finder->iterate()) {

    $token = $q->valueIs('if');
    $q->check(new PossibleWhitespace());


    $q->typePossible(T_WHITESPACE);

    $q->valueIs('->');
    $q->typePossible(T_WHITESPACE);
    $q->valueIs('fetchAll');
    $q->valueIs('(');
    $last = $q->valueIs($token->getValue());
    $nex = $q->valueIs('->');
    $select = $q->valueIs('select');
    $b1 = $q->valueIs('(');
    $b2 = $q->valueIs(')');

    if (!$q->valid()) {
      continue;
    }

    $b1->remove();
    $b2->remove();
    $select->remove();
    $nex->remove();
    $last->remove();

    $token->appendToValue('->select()');
//    $last->setValue('$database');
//
//    if (!empty($possibleSpace)) {
//      $possibleSpace->setValue("");
//    }

  }

  echo $finder;


  //  while ($q = $finder->getNext()) {
  //
  //    $token = $q->typeIs(T_VARIABLE);
  //    $ref = $q->valueIs('->');
  //    $fetchAll = $q->valueIs('fetchAll');
  //    $start = $q->sectionStart('(');
  //    $q->valueIs($token->getValue());
  //    $q->valueIs('->');
  //    $q->valueIs('section');
  //    $q->any();
  //    $end = $q->sectionEnd(')');
  //
  //    $q->remove($token, $ref, $fetchAll, $start);
  //
  //    $end->appendValue('->fetchAll()');
  //
  //  }