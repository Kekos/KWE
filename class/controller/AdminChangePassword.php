<?php
/**
 * KWF Controller: AdminChangePassword
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-06-26
 * @version 1.0
 */

class AdminChangePassword extends Controller
  {
  private $db = null;

  public function before()
    {
    if (!access::$is_logged_in)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    }

  public function _default()
    {
    if ($this->request->post('save_password'))
      {
      if (!$this->editUserPassword() && $this->request->ajax_request)
        {
        return;
        }
      }

    $this->view = new View('admin/change-password');
    }

  private function editUserPassword()
    {
    $errors = array();
    $password_old = $this->request->post('password_old');
    $password = $this->request->post('password');
    $password_repeat = $this->request->post('password_repeat');

    if (md5($password_old) != access::$user->password)
      $errors['password_old'] = 'Du angav fel lösenord.';
    if (!access::$user->setPassword($password))
      $errors['password'] = 'Skriv in ett längre lösenord.';

    if (md5($password_repeat) != md5($password))
      $errors['password_repeat'] = 'De två lösenorden du skrev in stämde inte överens.';

    if (!count($errors))
      {
      access::$user->save();
      $this->response->addInfo('Du fick ett nytt lösenord.');
      return true;
      }
    else
      {
      $this->response->addFormError($errors);
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