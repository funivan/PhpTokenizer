<?php

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\Exception;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/26/13
   */
  class Helper {

    /**
     * @param $string
     * @return Token[]
     * @throws Exception
     */
    public static function getTokensFromString($string) {
      $tokens = token_get_all($string);

      foreach ($tokens as $index => $tokenData) {

        if (!is_array($tokenData)) {
          $previousIndex = $index - 1;

          /** @var Token $previousToken */
          $previousToken = $tokens[$previousIndex];

          $tokenData = array(
            Token::INVALID_TYPE,
            $tokenData,
            $previousToken->getLine(),
          );
        }

        $token = new Token($tokenData);
        $token->setIndex($index);
        $tokens[$index] = $token;

      }

      return $tokens;
    }

    /**
     * @param Collection $collection
     * @return string
     */
    public static function dump(Collection $collection) {
      $string = "<pre>\n";
      foreach ($collection as $token) {
        $string .= '[' . $token->getTypeName() . ']' . "\n" . print_r($token->getData(), true) . "\n";
      }
      $string .= " </pre > ";
      return $string;
    }


  }