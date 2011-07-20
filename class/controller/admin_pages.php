<?php
/**
 * KWF Controller: admin_pages
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-07-11
 * @version 2.1
 */

class admin_pages extends controller
  {
  private $db = null;
  private $model_page = null;
  private $model_page_controller = null;
  private $page = false;
  private $subpage = false;
  private $active_page = false;
  private $ignore_view = false;

  public function before($action = false, $page_url = false, $subpage_url = false)
    {
    if (!access::$is_logged_in || !access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $this->db = db_mysqli::getInstance();
    $this->model_page = new page_model($this->db);
    $this->model_page_controller = new page_controller_model($this->db);

    if ($action && $page_url)
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
      $this->active_page = $this->subpage;
      }
    else if ($this->page)
      {
      $this->active_page = $this->page;
      }

    if ($this->active_page && access::$user->rank == 1)
      $this->active_page->permission = PERMISSION_ADD | PERMISSION_EDIT | PERMISSION_DELETE;
    }

  public function _default()
    {
    if (!$this->subpage)
      {
      if ($this->request->post('new_page') && 
          (access::$user->rank == 1 || $this->page->permission & PERMISSION_ADD))
        {
        $this->newPage();
        }
      }
    else
      {
      if ($this->request->post('new_page') && access::$user->rank == 1)
        {
        $this->newPage();
        }
      }

    $this->listPages();
    }

  public function up()
    {
    if (!$this->active_page)
      return;

    if (!($this->active_page->permission & PERMISSION_EDIT))
      return $this->response->addError('Du har inte tillräckliga rättigheter att redigera denna sida.');

    $sorter = new step_sorter($this->model_page);
    if ($sorter->up($this->active_page, array($this->active_page->parent, $this->active_page->id)))
      {
      $this->response->addInfo('Sidan ' . htmlspecialchars($this->active_page->title) . ' flyttades uppåt i menyn.');
      }
    else
      {
      $this->response->addError('Det gick inte att flytta sidan.');
      }

    if (!$this->subpage)
      $this->page = $this->active_page = false;

    $this->listPages();
    }

  public function down()
    {
    if (!$this->active_page)
      return;

    if (!($this->active_page->permission & PERMISSION_EDIT))
      return $this->response->addError('Du har inte tillräckliga rättigheter att redigera denna sida.');

    $sorter = new step_sorter($this->model_page);
    if ($sorter->down($this->active_page, array($this->active_page->parent, $this->active_page->id)))
      {
      $this->response->addInfo('Sidan ' . htmlspecialchars($this->active_page->title) . ' flyttades nedåt i menyn.');
      }
    else
      {
      $this->response->addError('Det gick inte att flytta sidan.');
      }

    if (!$this->subpage)
      $this->page = $this->active_page = false;

    $this->listPages();
    }

  public function delete()
    {
    if (!$this->active_page)
      return;

    if (!($this->active_page->permission & PERMISSION_DELETE))
      return $this->response->addError('Du har inte tillräckliga rättigheter att ta bort denna sida.');

    if ($this->request->post('delete_page_yes'))
      {
      if ($this->subpage)
        $this->deleteSubpage();
      else
        $this->deletePage();

      $this->listPages();
      }
    else if ($this->request->post('delete_page_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new view('admin/delete-page', array('page' => $this->active_page));
      }
    }

  private function listPages()
    {
    $data['active_page'] = $this->active_page;
    $data['new_page'] = $this->request->post('new_page');
    $data['pages'] = (!$this->page ? $this->model_page->fetchPageList(0, 1) : 
        $this->model_page->fetchSubPageList($this->page->id, 0, 1));
    $this->view = new view('admin/list-pages', $data);
    }

  private function newPage()
    {
    $errors = array();
    $title = $this->request->post('title');

    $this->active_page = new kpage($this->model_page);

    if (!$this->active_page->setTitle($title))
      $errors[] = 'Skriv in en längre sidtitel.';

    if (!count($errors))
      {
      $url = urlSafe($title);
      $parent = 0;

      /* Is this new page a subpage? */
      if ($this->page)
        {
        $url = $this->page->url . '/' . $url;
        $parent = $this->page->id;
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
      $sorter = new step_sorter($this->model_page);
      $sorter->append($this->active_page, array($parent, 0));

      $this->active_page->setUrl($url . $i);
      $this->active_page->setParent($parent);
      $this->active_page->setPublic(0);
      $this->active_page->setShowInMenu(0);
      $this->active_page->setCreator(access::$user->id);
      $this->active_page->setCreated(time());

      /* Set last editor and save */
      $this->updateEdited();

      $this->response->redirect(urlModr('page', 'list', $url));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function updateEdited()
    {
    $this->active_page->setEditor(access::$user->id);
    $this->active_page->setEdited(time());
    $this->active_page->save();
    }

  private function deletePage()
    {
    $model_permission = new permission_model($this->db);

    /* Delete all permissions bound to this page and delete all subpages */
    foreach ($this->model_page->fetchSubPageList($this->page->id, 0) as $subpage)
      {
      $model_permission->deleteByPage($subpage->id);
      $this->model_page->delete($subpage->id);
      $this->model_page_controller->deleteByPage($subpage->id);
      }

    /* Delete this page's controllers */
    $this->model_page_controller->deleteByPage($this->page->id);

    $this->response->addInfo('Sidan ' . htmlspecialchars($this->page->title) . ' och dess undersidor togs bort.');

    $this->page = false;
    $this->active_page = false;
    }

  private function deleteSubpage()
    {
    /* Delete all permissions bound to this subpage */
    $model_permission = new permission_model($this->db);
    $model_permission->deleteByPage($this->subpage->id);

    /* Delete the page and it's controllers */
    $this->active_page->delete();
    $this->model_page_controller->deleteByPage($this->subpage->id);

    $this->response->addInfo('Undersidan ' . htmlspecialchars($this->subpage->title) . ' togs bort.');

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