<?php


/**
 * @desc Inflection class to convert singular and plural noun strings
 * @author SDK (steve@eardish.com)
 * @date 2012-07-01
 */
class inflect {

  // List of plural patterns
  static $plural = array(
    '/(quiz)$/i'               => "$1zes",
    '/^(ox)$/i'                => "$1en",
    '/([m|l])ouse$/i'          => "$1ice",
    '/(matr|vert|ind)ix|ex$/i' => "$1ices",
    '/(x|ch|ss|sh)$/i'         => "$1es",
    '/([^aeiouy]|qu)y$/i'      => "$1ies",
    '/(hive)$/i'               => "$1s",
    '/(?:([^f])fe|([lr])f)$/i' => "$1$2ves",
    '/(shea|lea|loa|thie)f$/i' => "$1ves",
    '/sis$/i'                  => "ses",
    '/([ti])um$/i'             => "$1a",
    '/(tomat|potat|ech|her|vet)o$/i'=> "$1oes",
    '/(bu)s$/i'                => "$1ses",
    '/(alias)$/i'              => "$1es",
    '/(octop)us$/i'            => "$1i",
    '/(ax|test)is$/i'          => "$1es",
    '/(us)$/i'                 => "$1es",
    '/s$/i'                    => "s",
    '/$/'                      => "s"
  );

  // List of singular patterns
  static $singular = array(
    '/(quiz)zes$/i'             => "$1",
    '/(matr)ices$/i'            => "$1ix",
    '/(vert|ind)ices$/i'        => "$1ex",
    '/^(ox)en$/i'               => "$1",
    '/(alias)es$/i'             => "$1",
    '/(octop|vir)i$/i'          => "$1us",
    '/(cris|ax|test)es$/i'      => "$1is",
    '/(shoe)s$/i'               => "$1",
    '/(o)es$/i'                 => "$1",
    '/(bus)es$/i'               => "$1",
    '/([m|l])ice$/i'            => "$1ouse",
    '/(x|ch|ss|sh)es$/i'        => "$1",
    '/(m)ovies$/i'              => "$1ovie",
    '/(s)eries$/i'              => "$1eries",
    '/([^aeiouy]|qu)ies$/i'     => "$1y",
    '/([lr])ves$/i'             => "$1f",
    '/(tive)s$/i'               => "$1",
    '/(hive)s$/i'               => "$1",
    '/(li|wi|kni)ves$/i'        => "$1fe",
    '/(shea|loa|lea|thie)ves$/i'=> "$1f",
    '/(^analy)ses$/i'           => "$1sis",
    '/((a)naly|(b)a|(d)iagno|(p)arenthe|(p)rogno|(s)ynop|(t)he)ses$/i'  => "$1$2sis",
    '/([ti])a$/i'               => "$1um",
    '/(n)ews$/i'                => "$1ews",
    '/(h|bl)ouses$/i'           => "$1ouse",
    '/(corpse)s$/i'             => "$1",
    '/(us)es$/i'                => "$1",
    '/s$/i'                     => ""
  );

  // List of irregular singular -> plural patterns
  static $irregular = array(
    'move'   => 'moves',
    'foot'   => 'feet',
    'goose'  => 'geese',
    'sex'    => 'sexes',
    'child'  => 'children',
    'man'    => 'men',
    'tooth'  => 'teeth',
    'person' => 'people'
  );

  // List of patterns where singular == plural
  static $uncountable = array(
    'sheep',
    'fish',
    'deer',
    'series',
    'species',
    'money',
    'rice',
    'information',
    'equipment'
  );


  /**
   * @desc Pluralize the singular noun string
   * @author SDK (steve@eardish.com)
   * @date 2012-07-01
   * @param str $string The string of the singular noun to pluralize
   * @return str Returns the plural version of the string
  */
  public static function pluralize($string) {

    // if empty string, then return it empty
    if(empty($string)) return $string;

    // save some time in the case that singular and plural are the same
    if(in_array(strtolower($string), self::$uncountable)) {
      return $string;
    }

    // check for irregular singular forms
    if(count(self::$irregular)) {
      foreach(self::$irregular as $pattern => $result) {
        $pattern = '/' . $pattern . '$/i';
        if(preg_match($pattern, $string)) {
          return preg_replace( $pattern, $result, $string);
        }
      }
    }

    // check for matches using regular expressions
    if(count(self::$plural)) {
      foreach(self::$plural as $pattern => $result) {
        if(preg_match($pattern, $string)) {
          return preg_replace($pattern, $result, $string);
        }
      }
    }

    return $string;
  } // end function pluralize


  /**
   * @desc Singularize the plural noun string
   * @author SDK (steve@eardish.com)
   * @date 2012-07-01
   * @param str $string The string of the plural noun to singularize
   * @return str Returns the singular version of the string
  */
  public static function singularize($string) {

    // if empty string, then return it empty
    if(empty($string)) return $string;

    // save some time in the case that singular and plural are the same
    if(in_array(strtolower($string), self::$uncountable)) {
      return $string;
    }

    // check for irregular plural forms
    if(count(self::$irregular)) {
      foreach(self::$irregular as $result => $pattern) {
        $pattern = '/' . $pattern . '$/i';
        if(preg_match($pattern, $string)) {
          return preg_replace( $pattern, $result, $string);
        }
      }
    }

    // check for matches using regular expressions
    if(count(self::$singular)) {
      foreach(self::$singular as $pattern => $result) {
        if(preg_match($pattern, $string)) {
          return preg_replace($pattern, $result, $string);
        }
      }
    }

    return $string;
  } // end function singularize


  /**
   * @desc Build a string that provides a count and the corresponding inflection of the string
   * @author SDK (steve@eardish.com)
   * @date 2012-07-01
   * @param int $count The count of the noun to inflect
   * @param str $string The string of the noun in singular form
   * @return str Returns a string showing the count of the noun and corresponding inflection
  */
  public static function pluralize_if($count, $string) {

    // if empty string, then return it empty
    if(empty($string)) return $string;

    // If only 1, return with singular form, otherwise pluralize
    if($count == 1) {
      return "1 $string";
    } else {
      return $count . " " . self::pluralize($string);
    }

  } // end function pluralize_if


} // end class inflect

