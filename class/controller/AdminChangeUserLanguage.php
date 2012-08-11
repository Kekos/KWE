<?php
/**
 * KWF Controller: AdminChangeUserLanguage
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-12
 * @version 1.0
 */

class AdminChangeUserLanguage extends Controller
  {
  private $db = null;
  private $model_language = null;

  public function before()
    {
    if (!Access::$is_logged_in)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_language = new LanguageModel($this->db);
    }

  public function _default()
    {
    if ($this->request->post('save_language'))
      {
      if (!$this->editUserLanguage() && $this->request->ajax_request)
        {
        return;
        }
      }

    $data['languages'] = $this->model_language->fetchAll();
    $this->view = new View('admin/change-user-language', $data);
    }

  private function editUserLanguage()
    {
    $errors = array();
    $language = $this->request->post('language');

    if (!Access::$user->setLanguage($language))
      $errors[] = __('CHANGE_LANG_ERROR');

    if (!count($errors))
      {
      Access::$user->save();

      $language = $this->model_language->fetch($language);
      Language::set($language->code);
      Language::load('admin');

      $this->response->addInfo(__('CHANGE_LANG_INFO'));
      return true;
      }
    else
      {
      $this->response->addError($errors);
      return false;
      }
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