<?php

  declare(strict_types=1);

  namespace Funivan\PhpTokenizer;

  use Funivan\PhpTokenizer\Exception\Exception;

  /**
   * @author Ivan Shcherbak <dev@funivan.com> 11/26/13
   */
  class Helper {

    /**
     * Convert php code to array of tokens
     *
     * @param string $code
     * @return Token[]
     * @throws Exception
     */
    public static function getTokensFromString($code) {
      try {
        $tokens = token_get_all($code, TOKEN_PARSE);
      } catch (\ParseError $e) {
        // with TOKEN_PARSE flag, the function throws on invalid code
        // let's just ignore the error and tokenize the code without the flag
        $tokens = token_get_all($code);
      }

      foreach ($tokens as $index => $tokenData) {

        if (!is_array($tokenData)) {
          $previousIndex = $index - 1;

          /** @var Token $previousToken */
          $previousToken = $tokens[$previousIndex];
          $line = $previousToken->getLine() + substr_count($previousToken->getValue(), "\n");
          $tokenData = [
            Token::INVALID_TYPE,
            $tokenData,
            $line,
          ];
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
      $string = '<pre>' . "\n";
      foreach ($collection as $token) {
        $string .= '[' . $token->getTypeName() . ']' . "\n" . print_r($token->getData(), true) . "\n";
      }
      $string .= ' </pre > ';
      return $string;
    }


  }
