<?php
/**
 * KWE Controller: AdminLogin
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-30
 * @version 2.2
 */

class AdminLogin extends Controller
  {
  private $db;
  private $user_model;
  private $settings;

  public function _default($logout = false)
    {
    $this->db = DbMysqli::getInstance();
    $this->user_model = new UserModel($this->db);
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

      // Store all available controllers in a session so it can be used in navigation menu
      $model_controller = new ControllerModel($this->db);
      $controllers = $model_controller->fetchAllWithPermissions($user->id, ($user->rank == 1));
      setControllerMenuSession($controllers, $this->request);

      $this->response->redirect(urlModr());
      }
    else
      {
      $this->response->addError(__('LOGIN_ERROR'));
      }
    }

  private function doLogout()
    {
    if ($this->request->session->get($this->settings->session_name))
      {
      $this->user_model->clearOnlineCache(time());
      Access::$user->setOnline(0);
      Access::$user->save();

      $this->request->session->delete($this->settings->session_name);
      $this->request->session->delete('controllers');
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