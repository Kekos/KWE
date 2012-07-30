<?php
/**
 * KWF Controller: AdminEditPage
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-27
 * @version 1.0
 */

class AdminEditPage extends Controller
  {
  private $db = null;
  private $model_page = null;
  private $model_page_controller = null;
  private $kpage = false;
  private $subpage = false;
  private $active_page = false;
  private $controller = false;

  public function before($action = false, $page_url = false, $subpage_url = false, $controller_id = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_page = new PageModel($this->db);
    $this->model_page_controller = new PageControllerModel($this->db);

    // If an other action than "show" is selected, the controller ID might be in parameter 3 instead (subpage_url)
    if ($action != 'show' && $controller_id === false)
      {
      $controller_id = $subpage_url;
      $subpage_url = false;
      }

    if ($page_url)
      {
      if (!$this->kpage = $this->model_page->fetchPagePermission($page_url, Access::$user->id))
        return $this->response->addInfo(__('PAGES_INFO_NOT_FOUND', htmlspecialchars($page_url)));

      if ($subpage_url)
        {
        if (!$this->subpage = $this->model_page->fetchPagePermission($page_url . '/' . $subpage_url, Access::$user->id))
          return $this->response->addInfo(__('PAGES_INFO_NOT_FOUND', htmlspecialchars($page_url . '/' . $subpage_url)));
        }
      }

    if ($this->subpage)
      {
      $this->active_page = &$this->subpage;
      }
    else if ($this->kpage)
      {
      $this->active_page = &$this->kpage;
      }
    else
      {
      $this->response->redirect(urlModr('page'));
      }

    // Super Administrators (rank 1) can access everything, so give them all permissions
    if ($this->active_page && Access::$user->rank == 1)
      $this->active_page->permission = PERMISSION_ADD | PERMISSION_EDIT | PERMISSION_DELETE;

    if (!($this->active_page->permission & PERMISSION_EDIT))
      {
      $this->active_page = false;
      return $this->response->addError(__('PAGES_ERROR_NO_PERMISSION'));
      }

    if ($controller_id)
      {
      if (!$this->controller = $this->model_page_controller->fetch($controller_id))
        {
        $this->active_page = false;
        $this->response->addError(__('EDIT_PAGE_ERROR_MODULE_NOT_FOUND', $controller_id));
        }
      }
    }

  public function _default()
    {
    $this->response->redirect(urlModr('page'));
    }

  public function show()
    {
    // First, check if an page could be found in before() (might be removed if user has no permission)
    if (!$this->active_page)
      return;

    // Did the user save changes (like title and visibility)
    if ($this->request->post('edit_page'))
      {
      $this->editPage();
      if ($this->request->ajax_request)
        {
        return;
        }
      }
    // Did the user add a new controller to page
    else if ($this->request->post('add_controller'))
      {
      $this->addController();
      }

    $model_controller = new ControllerModel($this->db);

    $data['active_page'] = $this->active_page;
    $data['installed_controllers'] = $model_controller->fetchAll();
    $data['controllers'] = $this->model_page_controller->fetchAll($this->active_page->id);
    $this->view = new View('admin/edit-page', $data);
    }

  public function edit()
    {
    // First, check if an page could be found in before() (might be removed if user has no permission)
    if (!$this->active_page || !$this->controller)
      return;

    if ($this->request->post('save_controller'))
      {
      $this->saveController();
      }

    $data['active_page'] = $this->active_page;
    $data['controller'] = $this->controller;
    $this->view = new View('admin/edit-page-controller', $data);
    }

  public function delete()
    {
    // First, check if an page could be found in before() (might be removed if user has no permission)
    if (!$this->active_page || !$this->controller)
      return;

    if ($this->request->post('delete_pcontroller_yes'))
      {
      $this->deleteController();
      $this->show();
      }
    else if ($this->request->post('delete_pcontroller_no'))
      {
      $this->show();
      }
    else
      {
      $data['active_page'] = $this->active_page;
      $data['controller'] = $this->controller;
      $this->view = new View('admin/delete-page-controller', $data);
      }
    }

  public function orderup()
    {
    // First, check if an page could be found in before() (might be removed if user has no permission)
    if (!$this->active_page || !$this->controller)
      return;

    $sorter = new StepSorter($this->model_page_controller);
    if ($sorter->up($this->controller, array($this->active_page->id, $this->controller->id)))
      {
      $this->updateEdited();
      $this->response->addInfo(__('EDIT_PAGE_INFO_MODULE_UP'));
      }
    else
      {
      $this->response->addError(__('EDIT_PAGE_ERROR_MOD_NO_MOVE'));
      }

    $this->show();
    }

  public function orderdown()
    {
    // First, check if an page could be found in before() (might be removed if user has no permission)
    if (!$this->active_page || !$this->controller)
      return;

    $sorter = new StepSorter($this->model_page_controller);
    if ($sorter->down($this->controller, array($this->active_page->id, $this->controller->id)))
      {
      $this->updateEdited();
      $this->response->addInfo(__('EDIT_PAGE_INFO_MODULE_DOWN'));
      }
    else
      {
      $this->response->addError(__('EDIT_PAGE_ERROR_MOD_NO_MOVE'));
      }

    $this->show();
    }

  private function updateEdited()
    {
    $this->active_page->setEditor(Access::$user->id);
    $this->active_page->setEdited(time());
    $this->active_page->save();
    }

  private function editPage()
    {
    $errors = array();
    $title = $this->request->post('title');
    $public = $this->request->post('public');
    $show_in_menu = $this->request->post('show_in_menu');

    if (!$this->active_page->setTitle($title))
      $errors[] = __('PAGES_ERROR_NAME_LENGTH');
    if (!$this->active_page->setPublic($public))
      $errors[] = __('EDIT_PAGE_ERROR_PUBLISH');
    if (!$this->active_page->setShowInMenu($show_in_menu))
      $errors[] = __('EDIT_PAGE_ERROR_VISIBLE');

    if (!count($errors))
      {
      $this->updateEdited();
      $this->response->addInfo(__('EDIT_PAGE_INFO_SAVED', htmlspecialchars($title)));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function addController()
    {
    $errors = array();
    $controller = $this->request->post('controller');

    $this->controller = new PageController($this->model_page_controller);

    /* Append this controller to bottom of page with step sorter */
    $sorter = new StepSorter($this->model_page_controller);
    $sorter->append($this->controller, array($this->active_page->id, 0));

    $this->controller->setPage($this->active_page->id);
    $this->controller->setController($controller);
    $this->controller->setContent('');
    $this->controller->save();
    $this->updateEdited();
    $this->response->addInfo(__('EDIT_PAGE_INFO_MODULE_ADD', htmlspecialchars($this->active_page->title)));
    }

  private function saveController()
    {
    $errors = array();
    $error = 0;
    $content = $this->request->post('content_' . $this->controller->id);

    $controller_path = BASE . 'class/controller/AdminPage.' . $this->controller->class_name . '.php';
    if (file_exists($controller_path))
      $error = require($controller_path);

    if (!$error)
      {
      $this->controller->setContent($content);
      $this->controller->save();
      $this->updateEdited();
      $this->response->addInfo(__('EDIT_PAGE_INFO_MODULE_SAVE', htmlspecialchars($this->controller->name)));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function deleteController()
    {
    $this->controller->delete();
    $this->updateEdited();
    $this->response->addInfo(__('EDIT_PAGE_INFO_MODULE_DELETE', htmlspecialchars($this->controller->name)));
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