<?php
/**
 * KWF Controller: AdminPages
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-11-25
 * @version 2.2
 */

class AdminPages extends Controller
  {
  private $db = null;
  private $model_page = null;
  private $model_page_controller = null;
  private $model_language = null;
  private $kpage = false;
  private $subpage = false;
  private $active_page = false;
  private $ignore_view = false;

  public function before($action = false, $language = false, $page_url = false, $subpage_url = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_page = new PageModel($this->db);
    $this->model_page_controller = new PageControllerModel($this->db);
    $this->model_language = new LanguageModel($this->db);

    if ($action && $page_url)
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
      $this->active_page = $this->subpage;
      }
    else if ($this->kpage)
      {
      $this->active_page = $this->kpage;
      }

    if ($this->active_page && Access::$user->rank == 1)
      $this->active_page->permission = PERMISSION_ADD | PERMISSION_EDIT | PERMISSION_DELETE;
    }

  public function _default($language = '')
    {
    if (!$this->kpage)
      {
      if ($this->request->post('language'))
        {
        $language = $this->request->post('language');
        }
      else if (!is_numeric($language))
        {
        $language = Access::$user->language;
        }

      $data['languages'] = $this->model_language->fetchAll();
      }
    else
      {
      $language = $this->kpage->language;
      }

    if ($this->request->post('order'))
      {
      $arr = $this->request->post('order');
      $order = array_keys($arr)[0];
      $id = array_keys($arr[$order])[0];
      $page = $this->model_page->fetchPagePermissionId($id, Access::$user->id);

      if ($page)
        {
        if (Access::$user->rank == 1)
          $page->permission = PERMISSION_ADD | PERMISSION_EDIT | PERMISSION_DELETE;

        $this->changeOrder($page, $order);
        }
      }

    $data['active_language'] = $language;
    $data['active_page'] = $this->active_page;
    $data['pages'] = (!$this->kpage ? $this->model_page->fetchPageList(0, 1, $language) : $this->model_page->fetchSubPageList($this->kpage->id, 0, 1));
    $this->view = new View('admin/list-pages', $data);
    }

  public function addnew($language = false)
    {
    if (!$this->subpage)
      {
      if ($this->request->post('edit_page') && (Access::$user->rank == 1 || $this->kpage->permission & PERMISSION_ADD))
        {
        $this->newPage($language);
        }
      }
    else
      {
      if ($this->request->post('edit_page') && Access::$user->rank == 1)
        {
        $this->newPage($language);
        }
      }

    $data['active_page'] = new Kpage($this->model_page);
    $data['language'] = $this->model_language->fetch($language);
    $this->view = new View('admin/edit-page', $data);
    }

  public function delete()
    {
    if (!$this->active_page)
      return;

    if (!($this->active_page->permission & PERMISSION_DELETE))
      return $this->response->addError(__('PAGES_ERROR_NO_PERMISSION'));

    if ($this->request->post('delete_page_yes'))
      {
      if ($this->subpage)
        $this->deleteSubpage();
      else
        $this->deletePage();

      $this->_default();
      }
    else if ($this->request->post('delete_page_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/delete-page', array('page' => $this->active_page));
      }
    }

  public function js_browse()
    {
    $json = array();
    $json['cd'] = '/' . ($this->kpage ? $this->kpage->title . '/' : '');
    $json['pages'] = (!$this->kpage ? $this->model_page->fetchPageList(0, 0) : 
        $this->model_page->fetchSubPageList($this->kpage->id, 0, 0));

    $this->response->setContentType('application/json');
    $this->response->addContent(json_encode($json));
    }

  private function newPage($language)
    {
    $errors = array();
    $title = $this->request->post('title');
    $public = $this->request->post('public');
    $show_in_menu = $this->request->post('show_in_menu');

    $this->active_page = new Kpage($this->model_page);

    if (!$this->active_page->setTitle($title))
      $errors[] = __('PAGES_ERROR_NAME_LENGTH');
    if (!$this->active_page->setLanguage($language))
      $errors[] = __('PAGES_ERROR_LANGUAGE');

    if (!count($errors))
      {
      $url = urlSafe($title);
      $parent = 0;

      /* Is this new page a subpage? */
      if ($this->kpage)
        {
        $url = $this->kpage->url . '/' . $url;
        $parent = $this->kpage->id;
        }

      /* Find first available URL for this page, start with empty counter (not 1) */
      $i = '';
      while ($this->model_page->getPage($url . $i))
        {
        if ($i == '')
          $i = 2;
        else
          ++$i;
        }

      /* Append this page to bottom of menu with step sorter */
      $sorter = new StepSorter($this->model_page);
      $sorter->append($this->active_page, array($parent, 0));

      $this->active_page->setUrl($url . $i);
      $this->active_page->setParent($parent);
      $this->active_page->setPublic($public);
      $this->active_page->setShowInMenu($show_in_menu);
      $this->active_page->setCreator(Access::$user->id);
      $this->active_page->setCreated(time());

      /* Set last editor and save */
      $this->updateEdited();

      $this->response->redirect(urlModr('edit-page', 'show', $url));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function updateEdited()
    {
    $this->active_page->setEditor(Access::$user->id);
    $this->active_page->setEdited(time());
    $this->active_page->save();
    }

  private function changeOrder($page, $order)
    {
    if (!($page->permission & PERMISSION_EDIT))
      return $this->response->addError(__('PAGES_ERROR_NO_PERMISSION'));

    $sorter = new StepSorter($this->model_page);
    if ($sorter->$order($page, array($page->parent, $page->id)))
      {
      $this->response->addInfo(__(($order == 'down' ? 'PAGES_INFO_DOWN' : 'PAGES_INFO_UP'), htmlspecialchars($page->title)));
      }
    else
      {
      $this->response->addError(__('PAGES_ERROR_NO_MOVE'));
      }
    }

  private function deletePage()
    {
    $model_permission = new PermissionModel($this->db);

    /* Delete all permissions bound to this page and delete all subpages */
    foreach ($this->model_page->fetchSubPageList($this->kpage->id, 0) as $subpage)
      {
      $model_permission->deleteByPage($subpage->id);
      $this->model_page->delete($subpage->id);
      $this->model_page_controller->deleteByPage($subpage->id);
      }

    /* Delete this page's controllers */
    $this->model_page_controller->deleteByPage($this->kpage->id);

    $this->response->addInfo(__('PAGES_INFO_DELETED', htmlspecialchars($this->kpage->title)));

    $this->kpage = false;
    $this->active_page = false;
    }

  private function deleteSubpage()
    {
    /* Delete all permissions bound to this subpage */
    $model_permission = new PermissionModel($this->db);
    $model_permission->deleteByPage($this->subpage->id);

    /* Delete the page and it's controllers */
    $this->active_page->delete();
    $this->model_page_controller->deleteByPage($this->subpage->id);

    $this->response->addInfo(__('PAGES_SUB_INFO_DELETED', htmlspecialchars($this->subpage->title)));

    $this->subpage = false;
    $this->active_page = false;
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