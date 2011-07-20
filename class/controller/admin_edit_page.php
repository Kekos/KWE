<?php
/**
 * KWF Controller: admin_edit_page
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-07-02
 * @version 1.0
 */

class admin_edit_page extends controller
  {
  private $db = null;
  private $model_page = null;
  private $model_page_controller = null;
  private $page = false;
  private $subpage = false;
  private $active_page = false;
  private $controller = false;
  private $ignore_view = false;

  public function before($page_url = false, $subpage_url = false)
    {
    if (!access::$is_logged_in || !access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = db_mysqli::getInstance();
    $this->model_page = new page_model($this->db);
    $this->model_page_controller = new page_controller_model($this->db);

    if ($page_url)
      {
      if (!$this->page = $this->model_page->fetchPagePermission($page_url, access::$user->id))
        return $this->response->addInfo('Hittade inte sidan med URL:en ' . htmlspecialchars($page_url));

      if ($subpage_url)
        {
        if (!$this->subpage = $this->model_page->fetchPagePermission($page_url . '/' . $subpage_url, access::$user->id))
          return $this->response->addInfo('Hittade inte sidan med URL:en ' . htmlspecialchars($page_url . '/' . $subpage_url));
        }
      }

    if ($this->subpage)
      {
      $this->active_page = &$this->subpage;
      }
    else if ($this->page)
      {
      $this->active_page = &$this->page;
      }
    else
      {
      $this->response->redirect(urlModr('page'));
      }

    if ($this->active_page && access::$user->rank == 1)
      $this->active_page->permission = PERMISSION_ADD | PERMISSION_EDIT | PERMISSION_DELETE;

    if (!($this->active_page->permission & PERMISSION_EDIT))
      return $this->response->addError('Du har inte tillräckliga rättigheter att redigera denna sida.');
    }

  public function _default()
    {
    if ($this->request->post('controller_id'))
      {
      if (!$this->controller = $this->model_page_controller->fetch($this->request->post('controller_id')))
        $this->response->addError('Hittade inte modulen med ID ' . $this->request->post('controller_id'));
      }

    $data['edit_page'] = 0;
    $data['add_controller'] = 0;
    $data['active_page'] = $this->active_page;

    if ($this->request->post('delete_controller') && $this->controller)
      {
      $data['controller'] = $this->controller;
      $this->view = new view('admin/delete-page-controller', $data);
      }
    else
      {
      if ($this->request->post('edit_page'))
        {
        $this->editPage();
        $data['edit_page'] = 1;
        }
      else if ($this->request->post('add_controller'))
        {
        $this->addController();
        $data['add_controller'] = 1;
        }
      else if ($this->request->post('save_controller') && $this->controller)
        $this->saveController();
      else if ($this->request->post('delete_pcontroller_yes') && $this->controller)
        $this->deleteController();
      else if ($this->request->post('controller_order_up') && $this->controller)
        $this->orderUpController();
      else if ($this->request->post('controller_order_down') && $this->controller)
        $this->orderDownController();

      if (!($this->ignore_view && $this->request->ajax_request))
        {
        $model_controller = new controller_model($this->db);

        $data['installed_controllers'] = $model_controller->fetchAll();
        $data['controllers'] = $this->model_page_controller->fetchAll($this->active_page->id);
        $this->view = new view('admin/edit-page', $data);
        }
      }
    }

  private function updateEdited()
    {
    $this->active_page->setEditor(access::$user->id);
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
      $errors[] = 'Skriv in en längre sidtitel.';
    if (!$this->active_page->setPublic($public))
      $errors[] = 'Du måste ange om sidan ska publiceras.';
    if (!$this->active_page->setShowInMenu($show_in_menu))
      $errors[] = 'Du måste ange om sidan ska vara synlig i menyn.';

    if (!count($errors))
      {
      $this->updateEdited();
      $this->response->addInfo('Sidan ' . htmlspecialchars($title) . ' är sparad med de nya uppgifterna.');
      }
    else
      {
      $this->response->addError($errors);
      }

    $this->ignore_view = true;
    }

  private function addController()
    {
    $errors = array();
    $controller = $this->request->post('controller');

    $this->controller = new page_controller($this->model_page_controller);

    /* Append this controller to bottom of page with step sorter */
    $sorter = new step_sorter($this->model_page_controller);
    $sorter->append($this->controller, array($this->active_page->id));

    $this->controller->setPage($this->active_page->id);
    $this->controller->setController($controller);
    $this->controller->setContent('');
    $this->controller->save();
    $this->updateEdited();
    $this->response->addInfo('Sidan ' . htmlspecialchars($this->active_page->title) . ' fick en ny modul tillagd.');
    }

  private function saveController()
    {
    $errors = array();
    $error = 0;
    $content = $this->request->post('content_' . $this->controller->id);

    $controller_path = BASE . 'class/controller/admin_page.' . $this->controller->class_name . '.php';
    if (file_exists($controller_path))
      $error = require($controller_path);

    if (!$error)
      {
      $this->controller->setContent($content);
      $this->controller->save();
      $this->updateEdited();
      $this->response->addInfo('Modulen ' . htmlspecialchars($this->controller->name) . ' sparades.');
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
    $this->response->addInfo('Modulen ' . htmlspecialchars($this->controller->name) . ' togs bort.');
    }

  private function orderUpController()
    {
    $sorter = new step_sorter($this->model_page_controller);
    if ($sorter->up($this->controller, array($this->active_page->id, $this->controller->id)))
      {
      $this->updateEdited();
      $this->response->addInfo('Modulen flyttades uppåt på sidan.');
      }
    else
      {
      $this->response->addError('Det gick inte att flytta sidan.');
      }

    $this->ignore_view = true;
    }

  private function orderDownController()
    {
    $sorter = new step_sorter($this->model_page_controller);
    if ($sorter->down($this->controller, array($this->active_page->id, $this->controller->id)))
      {
      $this->updateEdited();
      $this->response->addInfo('Modulen flyttades neråt på sidan.');
      }
    else
      {
      $this->response->addError('Det gick inte att flytta sidan.');
      }

    $this->ignore_view = true;
    }

  public function run()
    {
    if ($this->view != null && !($this->ignore_view && $this->request->ajax_request))
      {
      $this->response->setContentType('html');
      $this->response->addContent($this->view->compile($this->route, $this->params));
      }
    }
  }
?>