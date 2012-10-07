<?php
/**
 * KWF Controller: AdminUsers
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-06
 * @version 2.2
 */

class AdminUsers extends Controller
  {
  private $db = null;
  private $model_user = null;
  private $model_permission = null;
  private $model_controller = null;
  private $model_controller_permission = null;
  private $user = false;
  private $permissions = array();
  private $controller_permissions = array();

  public function before($action = false, $user_id = false)
    {
    if (!Access::$is_logged_in || Access::$user->rank > 1)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_user = new UserModel($this->db);

    if ($action && $user_id)
      {
      if (!$this->user = $this->model_user->fetch($user_id))
        return $this->response->addInfo(__('USERS_INFO_NOT_FOUND', htmlspecialchars($user_id)));
      }
    }

  public function _default()
    {
    if ($this->request->post('new_user'))
      {
      $this->newUser();
      }

    $this->listUsers();
    }

  public function edit()
    {
    if (!$this->user)
      return;

    if ($this->request->post('edit_user'))
      {
      $this->editUser();
      }
    else if ($this->request->post('edit_user_password'))
      {
      $this->editUserPassword();
      }

    $this->view = new View('admin/edit-user', array('user' => $this->user));
    }

  public function permissions()
    {
    if (!$this->user || $this->user->rank < 2)
      return;

    $this->model_permission = new PermissionModel($this->db);
    $this->model_controller_permission = new ControllerPermissionModel($this->db);
    $this->model_controller = new ControllerModel($this->db);

    $this->listPermissions();

    if ($this->request->post('save_user_permissions'))
      {
      $this->saveUserPermissions();
      }
    }

  public function delete()
    {
    if (!$this->user)
      return;

    if ($this->request->post('delete_user_yes'))
      {
      $this->deleteUser();
      $this->listUsers();
      }
    else if ($this->request->post('delete_user_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/delete-user', array('user' => $this->user));
      }
    }

  private function listUsers()
    {
    $data['users'] = $this->model_user->fetchAll();
    $data['new_user'] = $this->request->post('new_user');
    $this->view = new View('admin/list-users', $data);
    }

  private function listPermissions()
    {
    $pages = $this->model_permission->fetchByUser($this->user->id);
    foreach ($pages as $page)
      {
      if ($page->permission != null)
        $this->permissions[$page->id] = $page->permission;
      }

    $controllers = $this->model_controller->fetchAllWithPermissions($this->user->id, 1);
    foreach ($controllers as $controller)
      {
      if ($controller->user != null)
        $this->controller_permissions[$controller->id] = 1;
      }

    $data['user'] = $this->user;
    $data['pages'] = $pages;
    $data['controllers'] = $controllers;
    $this->view = new View('admin/user-permissions', $data);
    }

  private function newUser()
    {
    $errors = array();
    $username = $this->request->post('username');
    $password = $this->request->post('password');
    $name = $this->request->post('name');
    $rank = $this->request->post('rank');

    $this->user = new User($this->model_user);

    if (strlen($password) == 0)
      {
      $password = generatePassword(6);
      $this->user->setPassword($password);
      }
    else if (!$this->user->setPassword($password))
      $errors[] = __('USERS_ERROR_SHORT_PASSWORD');

    if (!$this->user->setUsername($username))
      $errors[] = __('USERS_ERROR_SHORT_USERNAME');
    if (!$this->user->setName($name))
      $errors[] = __('USERS_ERROR_SHORT_NAME');
    if (!$this->user->setRank($rank))
      $errors[] = __('USERS_ERROR_INVALID_RANK');

    if (!count($errors))
      {
      $this->user->setOnline(0);
      $this->user->setOnlineTime(0);
      $this->user->setLanguage(1);
      $this->user->save();
      $this->response->addInfo(__('USERS_INFO_NEW_USER', htmlspecialchars($username), htmlspecialchars($password)));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function editUser()
    {
    $errors = array();
    $name = $this->request->post('name');
    $rank = $this->request->post('rank');

    if (!$this->user->setName($name))
      $errors[] = __('USERS_ERROR_SHORT_NAME');
    if (!$this->user->setRank($rank))
      $errors[] = __('USERS_ERROR_INVALID_RANK');

    if (!count($errors))
      {
      $this->user->save();
      $this->response->addInfo(__('USERS_INFO_EDIT_USER', htmlspecialchars($this->user->username)));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function editUserPassword()
    {
    $errors = array();
    $password = $this->request->post('password');
    $password_repeat = $this->request->post('password_repeat');

    if (strlen($password) == 0)
      {
      $password = $password_repeat = generatePassword(6);
      $this->user->setPassword($password);
      }
    else if (!$this->user->setPassword($password))
      $errors[] = __('USERS_ERROR_SHORT_PASSWORD');

    $np_password = $password;
    $password = md5($password);
    $password_repeat = md5($password_repeat);
    if ($password != $password_repeat)
      $errors[] = __('CHANGE_PW_ERROR_MISSMATCH');

    if (!count($errors))
      {
      $this->user->save();
      $this->response->addInfo(__('USERS_INFO_NEW_PW', htmlspecialchars($this->user->username), $np_password));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function saveUserPermissions()
    {
    $permissions = $this->request->post('permissions');
    $controller_permissions = $this->request->post('controller_permissions');

    if (!is_array($permissions))
      $permissions = array();
    if (!is_array($controller_permissions))
      $controller_permissions = array();

    foreach ($permissions as $page => &$permission)
      {
      $p = 0;
      foreach ($permission as $perm)
        $p = $p | $perm;
      $permission = $p;

      if (array_key_exists($page, $this->permissions))
        {
        if ($this->permissions[$page] != $permission)
          $this->model_permission->update($this->user->id, $page, $permission);
        }
      else
        $this->model_permission->insert($this->user->id, $page, $permission);
      }

    foreach ($this->permissions as $page => $permission)
      {
      if (array_key_exists($page, $permissions))
        {
        if ($permissions[$page] != $permission)
          $this->model_permission->update($this->user->id, $page, $permission);
        }
      else
        $this->model_permission->delete($this->user->id, $page);
      }

    foreach ($controller_permissions as $controller => &$permission)
      {
      if (!array_key_exists($controller, $this->controller_permissions))
        $this->model_controller_permission->insert($this->user->id, $controller);
      }

    foreach ($this->controller_permissions as $controller => $permission)
      {
      if (!array_key_exists($controller, $controller_permissions))
        $this->model_controller_permission->delete($this->user->id, $controller);
      }

    $this->response->addInfo(__('USERS_INFO_PERM_SAVED'));

    $this->listPermissions();
    }

  private function deleteUser()
    {
    $this->response->addInfo(__('USERS_INFO_DELETED', htmlspecialchars($this->user->username)));

    $this->model_permission->deleteByUser($this->user->id);
    $this->model_controller_permission->deleteByUser($this->user->id);
    $this->user->delete();
    $this->user = false;
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