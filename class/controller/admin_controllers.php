<?php
/**
 * KWF Controller: admin_controllers
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-18
 * @version 2.2
 */

class admin_controllers extends controller
  {
  private $db = null;
  private $model_controller = null;
  private $model_controller_permission = null;
  private $controller = false;
  private $controllers = array();

  public function before($action = false, $controller_id = false)
    {
    if (!access::$is_logged_in || !access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = db_mysqli::getInstance();
    $this->model_controller = new controller_model($this->db);
    $this->model_controller_permission = new controller_permission_model($this->db);

    if ($action && $controller_id)
      {
      if ($this->controller = $this->model_controller->fetchByCName($controller_id, access::$user->id))
        {
        if (!file_exists(BASE . 'class/controller/admin_controller_' . $controller_id . '.php'))
          {
          $this->controller = false;
          return $this->response->addError('Modulen ' . htmlspecialchars($controller_id) . ' är korrupt (fil fattas).');
          }
        }
      else
        return $this->response->addError('Hittade inte modulen ' . htmlspecialchars($controller_id));
      }

    $this->controllers = $this->model_controller->fetchAllWithPermissions(access::$user->id, (access::$user->rank == 1));

    $nav = array();
    foreach ($this->controllers as $controller)
      {
      if ($controller->configurable)
        $nav[] = array($controller->name, '../' . $controller->class_name);
      }

    $this->response->data['subnavigation'] = $nav;
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
    if (!$this->controller || access::$user->rank != 1)
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
      $this->view = new view('admin/uninstall-controller', array('controller' => $this->controller));
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
      if (access::$user->rank == 1)
        $this->model_controller_permission->insert(access::$user->id, $this->controller->id, $favorite_config);
      else
        $this->model_controller_permission->update($favorite_config, access::$user->id, $this->controller->id);

      $this->response->redirect(urlModr($this->route));
      }
    else if ($this->request->post('mark_favorite_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else if ($this->controller->favorite)
      {
      if (access::$user->rank == 1)
        $this->model_controller_permission->delete(access::$user->id, $this->controller->id);
      else
        $this->model_controller_permission->update(0, access::$user->id, $this->controller->id);
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new view('admin/mark-favorite-controller', array('controller' => $this->controller));
      }
    }

  private function listControllers()
    {
    $data['controllers'] = $this->controllers;
    $data['install_controller'] = $this->request->post('install_controller');
    $this->view = new view('admin/list-controllers', $data);
    }

  private function installController()
    {
    $errors = array();
    $filename = $_FILES['file']['name'];

    if (empty($filename))
      $errors[] = 'Du måste ladda upp en installationsfil för att installera modulen.';
    else if (substr($filename, -4)  != '.kwe')
      $errors[] = 'Installationsfilen ska vara av typen .kwe';

    if (!count($errors))
      {
      move_uploaded_file($_FILES['file']['tmp_name'], $filename);

      $zip = new ZipArchive();
      if ($zip->open($filename) !== true)
        return $this->response->addError('Installationsfilen var ingen giltig KWE-fil (kunde inte öppna paketet).');

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

          $this->controller = new kcontroller($this->model_controller);

          if (!$this->controller->setName((string) $fileinfo_obj->name))
            $errors[] = 'Informationsfilen angav ett för kort modulnamn.';
          if (!$this->controller->setClassName((string) $fileinfo_obj->class_name))
            $errors[] = 'Informationsfilen angav ett för kort modulklassnamn.';
          if (!$this->controller->setConfigurable($fileinfo_obj->configurable))
            $errors[] = 'Informationsfilen angav felaktigt alternativ för konfiguration.';
          if (!$this->controller->setHasFavoriteConfig($fileinfo_obj->has_favorite_config))
            $errors[] = 'Informationsfilen angav felaktigt alternativ för favoritkonfiguration.';

          if (!count($errors))
            {
            $this->controller->save();
            if ($this->controller->id)
              {
              /* Then extract the rest of controller files */
              $zip->extractTo('../');

              /* Let the controller make custom installation stuff */
              $controller = 'admin_controller_' . $this->controller->class_name;
              $controller::install();

              $this->response->addInfo('Modulen har installerats.');
              }
            else
              {
              $this->response->addError('Det gick inte att registrera modulen.');
              }
            }
          else
            {
            $this->response->addError($errors);
            }
          }
        else
          {
          $this->response->addError('Installationsfilen var ingen giltig KWE-fil (informationsfilen var inte korrekt).');
          }

        unlink($fileinfo);
        }
      else
        {
        $this->response->addError('Installationsfilen var ingen giltig KWE-fil (hittade ingen informationsfil).');
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
    $controller = 'admin_controller_' . $this->controller->class_name;
    if ($controller::uninstall())
      {
      $model_controller_permission = new controller_permission_model($this->db);
      $model_controller_permission->deleteByController($this->controller->id);

      $this->controller->delete();
      $this->controller = false;
      $this->response->addInfo('Modulen har avinstallerats.');
      }
    else
      {
      $this->response->addError('Modulen kunde inte avinstalleras.');
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