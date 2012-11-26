<?php
/**
 * KWF Class: VisitorLanguage, contains methods for setting/getting the visitor's language settings
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-11-25
 * @version 1.0
 */

class VisitorLanguage
  {
  static $active_language = null;

  /*
   * Returns all languages
   *
   * @return array(Klanguage)
   */
  static function getAllLanguages()
    {
    $model_language = new LanguageModel(DbMysqli::getInstance());
    return $model_language->fetchAll();
    }

  /*
   * Returns the current language selected by user
   *
   * @return Klanguage
   */
  static function getLanguage()
    {
    if (self::$active_language == null)
      {
      if (isset($_SESSION['active_language']))
        {
        self::$active_language = $_SESSION['active_language'];
        }
      else
        {
        $languages = self::getAllLanguages();
        foreach ($languages as $language)
          {
          if ($language->code == Language::$language)
            {
            self::$active_language = $language;
            $_SESSION['active_language'] = $language;
            break;
            }
          }
        }
      }

    return self::$active_language;
    }

  /*
   * Sets the current selected language by user
   *
   * @param int $language The language
   * @return void
   */
  static function setActiveLanguage($language)
    {
    self::$active_language = $language;
    $_SESSION['active_language'] = $language;
    Language::set($language->code);
    }
  }
?>