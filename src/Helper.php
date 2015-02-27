<?php

  namespace Funivan\PhpTokenizer;

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
          
          if (!isset($tokens[$previousIndex])) {
            throw new Exception("Cant detect previous token and extract line from it. Possible invalid string. Previous index:" . $previousIndex);
          }
          /** @var Token $previousToken */
          $previousToken = $tokens[$previousIndex];

          $tokenData = array(
            Token::INVALID_TYPE,
            $tokenData,
            $previousToken->getLine(),
          );
        }

        $tokens[$index] = new Token($tokenData);

      }

      return $tokens;
    }
  }