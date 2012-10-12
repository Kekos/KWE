<?php
/**
 * KWF Controller: AdminControllers
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-12
 * @version 2.2
 */

class AdminControllers extends Controller
  {
  private $db = null;
  private $model_controller = null;
  private $model_controller_permission = null;
  private $controller = false;
  private $controllers = array();

  public function before($action = false, $controller_id = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_controller = new ControllerModel($this->db);
    $this->model_controller_permission = new ControllerPermissionModel($this->db);

    if ($action && $controller_id)
      {
      if ($this->controller = $this->model_controller->fetchByCName($controller_id, Access::$user->id))
        {
        if (!file_exists(BASE . 'class/controller/AdminController' . $controller_id . '.php'))
          {
          $this->controller = false;
          return $this->response->addError(__('MODULES_ERROR_CORRUPT', htmlspecialchars($controller_id)));
          }
        }
      else
        return $this->response->addError(__('MODULES_ERROR_NOT_FOUND', htmlspecialchars($controller_id)));
      }

    $this->fetchPermittedControllers();
    }

  private function fetchPermittedControllers($reset_session = false)
    {
    $this->controllers = $this->model_controller->fetchAllWithPermissions(Access::$user->id, (Access::$user->rank == 1));

    if ($reset_session)
      {
      setControllerMenuSession($this->controllers, $this->request);
      }
    }

  public function _default($action = false)
    {
    if ($this->request->post('install_controller'))
      {
      $this->installController();
      }

    $this->listControllers();
    }

  public function uninstall()
    {
    if (!$this->controller || Access::$user->rank != 1)
      return;

    if ($this->request->post('uninstall_controller_yes'))
      {
      $this->uninstallController();
      $this->listControllers();
      }
    else if ($this->request->post('uninstall_controller_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/uninstall-controller', array('controller' => $this->controller));
      }
    }

  public function favorite()
    {
    if (!$this->controller)
      return;

    if ($this->request->post('mark_favorite_yes'))
      {
      $favorite_config = $this->request->post('favorite_config');
      $favorite_config = ($favorite_config ? 2 : 1);
      if (Access::$user->rank == 1)
        $this->model_controller_permission->insert(Access::$user->id, $this->controller->id, $favorite_config);
      else
        $this->model_controller_permission->update($favorite_config, Access::$user->id, $this->controller->id);

      $this->response->redirect(urlModr($this->route));
      }
    else if ($this->request->post('mark_favorite_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else if ($this->controller->favorite)
      {
      if (Access::$user->rank == 1)
        $this->model_controller_permission->delete(Access::$user->id, $this->controller->id);
      else
        $this->model_controller_permission->update(0, Access::$user->id, $this->controller->id);
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/mark-favorite-controller', array('controller' => $this->controller));
      }
    }

  private function listControllers()
    {
    $data['controllers'] = $this->controllers;
    $data['install_controller'] = $this->request->post('install_controller');
    $this->view = new View('admin/list-controllers', $data);
    }

  private function installController()
    {
    $errors = array();
    $filename = $_FILES['file']['name'];

    if (empty($filename))
      $errors[] = __('MODULES_ERROR_NO_FILE');
    else if (substr($filename, -4)  != '.kwe')
      $errors[] = __('MODULES_ERROR_WRONG_TYPE');

    if (!count($errors))
      {
      move_uploaded_file($_FILES['file']['tmp_name'], $filename);

      $zip = new ZipArchive();
      if ($zip->open($filename) !== true)
        return $this->response->addError(__('MODULES_ERROR_INVALID_FILE'));

      /* First extract the Controller info file */
      if ($zip->extractTo('../', 'info.xml'))
        {
        /* Read the Controller info file (as XML) */
        $fileinfo = '../info.xml';
        libxml_use_internal_errors(false);
        $fileinfo_obj = simplexml_load_file($fileinfo);
        if (is_object($fileinfo_obj))
          {
          $errors = array();

          $this->controller = new Kcontroller($this->model_controller);

          if (!$this->controller->setName((string) $fileinfo_obj->name))
            $errors[] = __('MODULES_ERROR_INF_NAME_SHORT');
          if (!$this->controller->setClassName((string) $fileinfo_obj->class_name))
            $errors[] = __('MODULES_ERROR_INF_CLASS_SHORT');
          if (!$this->controller->setConfigurable($fileinfo_obj->configurable))
            $errors[] = __('MODULES_ERROR_INF_CONFIG_ALT');
          if (!$this->controller->setHasFavoriteConfig($fileinfo_obj->has_favorite_config))
            $errors[] = __('MODULES_ERROR_INF_CONFIG_FAV');

          if (!count($errors))
            {
            $this->controller->save();
            if ($this->controller->id)
              {
              /* Then extract the rest of controller files */
              $zip->extractTo('../');

              /* Let the controller make custom installation stuff, if it has an AdminController */
              $controller = 'AdminController' . $this->controller->class_name;
              if (is_file(BASE . 'class/controller/' . $controller . '.php'))
                {
                $controller::install();
                }

              $this->fetchPermittedControllers(true);
              $this->response->addInfo(__('MODULES_INFO_INSTALLED'));
              }
            else
              {
              $this->response->addError(__('MODULES_ERROR_REGISTER_INSTALL'));
              }
            }
          else
            {
            $this->response->addError($errors);
            }
          }
        else
          {
          $this->response->addError(__('MODULES_ERROR_CORRUPT_INFO'));
          }

        unlink($fileinfo);
        }
      else
        {
        $this->response->addError(__('MODULES_ERROR_NO_INFO'));
        }

      $zip->close();
      unlink($filename);
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function uninstallController()
    {
    $uninstalled = true;
    $controller = 'AdminController' . $this->controller->class_name;
    if (is_file(BASE . 'class/controller/' . $controller . '.php'))
      {
      $uninstalled = $controller::uninstall();
      }

    if ($uninstalled)
      {
      $this->model_controller_permission->deleteByController($this->controller->id);
      $this->fetchPermittedControllers(true);

      $this->controller->delete();
      $this->controller = false;
      $this->response->addInfo(__('MODULES_INFO_UNINSTALLED'));
      }
    else
      {
      $this->response->addError(__('MODULES_ERROR_UNINSTALL'));
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