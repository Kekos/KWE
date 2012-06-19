<?php
/**
 * KWE Controller: admin_login
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-06-19
 * @version 2.2
 */

class admin_login extends Controller
  {
  private $db;
  private $user_model;
  private $settings;

  public function _default($logout = false)
    {
    $this->db = DbMysqli::getInstance();
    $this->user_model = new user_model($this->db);
    $this->settings = json_decode($this->controller_data->content);

    if (!$this->request->session->get($this->settings->session_name))
      {
      $this->response->data['body_id'] = 'login';
      $this->view = new View('admin/login');
      }

    if ($this->request->post('username') && $this->request->post('password'))
      {
      $this->doLogin($this->request->post('username'), $this->request->post('password'));
      }
    else if (isset($this->request->params[0]) && $this->request->params[0] == 'logout')
      {
      $this->doLogout();
      }
    }

  private function doLogin($username, $password)
    {
    $user = $this->user_model->login($username, md5($password));

    if ($user && $user->rank < 3)
      {
      session_regenerate_id(true);
      $this->request->session->set($this->settings->session_name, $user->id);
      
      $this->user_model->clearOnlineCache(time());
      $user->setOnline(1);
      $user->save();

      $this->response->redirect(urlModr());
      }
    else
      {
      $this->response->addError('Du angav fel användarnamn och / eller lösenord.');
      }
    }

  private function doLogout()
    {
    if ($this->request->session->get($this->settings->session_name))
      {
      $this->user_model->clearOnlineCache(time());
      access::$user->setOnline(0);
      access::$user->save();

      $this->request->session->delete($this->settings->session_name);
      }

    $this->response->redirect(urlModr());
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