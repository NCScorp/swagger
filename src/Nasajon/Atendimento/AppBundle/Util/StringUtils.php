<?php

namespace Nasajon\Atendimento\AppBundle\Util;

/**
 * Funções utilitárias para strings
 *
 * @author Rodrigo Dirk <rodrigodirk@nasajon.com.br>
 */
class StringUtils {

    const CHARACTERStoREPLACE = array(',',':','ª','º','°',';','!','?','@','#','$','%','&','-','_','+','-','*','/', '\'', '"', '.', '=', '´', '`', '(', ')', '[', ']', '{', '}', '¨', '|', '<', '>', '\\');    
    
    /**
     * Remove as quebras de linhas de uma string
     *
     * @param string $string
     */
    public static function removeln($string) {
        return trim(preg_replace(array('/\n/', '/\r/','/\v/','/\s\s+/'), ' ', $string));
    }
    
    public static function removeTabulacoes($string) {
        return trim(preg_replace(array('/\t/'), ' ', $string));
    }

    public static function isGuid($string) {
        return preg_match("/^(\{)?[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}(?(1)\})$/i", $string);
    }

    /**
     * split email into name / address
     *
     * @param string $str
     * @return array
     */
    public static function email_split($str) {
        preg_match('/[\w\.\-+=*_]*@[\w\.\-+=*_]*/', $str, $email);
        $nome = str_replace(" <".$email[0].">","",$str);
        $nome = trim($nome, "'");
        $nome = trim($nome, '"');
        return array('nome' => trim($nome), 'email' => trim($email[0]));
    }

    public static function geraResumo($str) {
        $str = strip_tags($str);
        $str = trim(str_replace(array('<', '>', '&gt;', '&lt;'), '' , $str));
        $str = StringUtils::removeln($str);
        $str = mb_substr($str, 0, 140, 'UTF-8');

        return $str;
    }
    /*
     * remove non break space
     * @param string $str
     * @return string
     */
    public static function nonBreakSpace($str){
      $str = str_replace("\xc2\xa0", " ", $str);
      return $str;
    }

    public static function removeAcentos($str) {

        $map = array(
            'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'Á' => 'A', 'À' => 'A', 'Ã' => 'A', 'Â' => 'A',
            'é' => 'e', 'ê' => 'e', 'É' => 'E', 'Ê' => 'E',
            'í' => 'i', 'Í' => 'I',
            'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
            'ú' => 'u', 'ü' => 'u', 'Ú' => 'U', 'Ü' => 'U',
            'ç' => 'c', 'Ç' => 'C'
        );

        return strtr($str, $map);
    }

    public static function removeCaracteresInvalidosNaAWS($str){
      $caracteres_invalidos = ['&', '$', '@', '=', ';', '+', ',', '?'];
      foreach($caracteres_invalidos as $value) {
          $str = str_replace($value, "", $str);
      }
      return $str;
    }
    
    public static function removeCaracteresInvalidosNoTsQuery($filtro) {
        return trim(str_replace(self::CHARACTERStoREPLACE, ' ' , $filtro));
    }
    
    public static function ofuscateEmail($email) {
    $split = explode("@", $email);

    $name = $split[0];

    if (strlen($name) > 4) {
      $newSender = ($name[0] . str_repeat("*", (strlen($name) - 2)) . $name[strlen($name) - 1]);
    } elseif (strlen($name) > 1) {
      $newSender = ($name[0] . str_repeat("*", (strlen($name) - 1)));
    } else {
      $newSender = '*';
    }

    $newEmail = $newSender . "@" . $split[1];

    return $newEmail;
  }

  public static function generateFileDescription($str) {
    $file_info = pathinfo($str);
    $name = mb_substr($file_info['filename'], 0, 199 - strlen($file_info['extension']), 'UTF-8');
    return $name . '.' . $file_info['extension'];
  }

  public static function isEmail($str) {
    return filter_var($str, FILTER_VALIDATE_EMAIL);
  }

}