<?php
/**
 * KWF Controller: LanguageSwitch
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-11-25
 * @version 1.0
 */

class LanguageSwitch extends Controller
  {
  public function _default()
    {
    if ($this->request->post('language_code'))
      {
      $selected = $this->request->post('language_code');
      $redirect = $this->request->post('redirect');

      $languages = VisitorLanguage::getAllLanguages();
      foreach ($languages as $language)
        {
        if ($language->id == $selected)
          {
          VisitorLanguage::setActiveLanguage($language);
          break;
          }
        }
      }

    $this->response->redirect($redirect);
    }

  public function run()
    {
    if ($this->view != null)
      {
      $this->response->setContentType('html');
      $this->response->addContent($this->view->compile($this->route, $this->params));
      }
    }
  }
?>