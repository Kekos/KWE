<?php
/**
 * KWF Controller: AdminControllerNews
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-02
 * @version 2.2
 */

class AdminControllerNews extends Controller
  {
  private $db = null;
  private $model_news = null;
  private $news = false;

  public function before($action = false, $news_id = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator || !Access::hasControllerPermission('News'))
      {
      $this->response->redirect(urlModr());
      }

    loadFallbackLanguage('News');

    $this->db = DbMysqli::getInstance();
    $this->model_news = new NewsModel($this->db);
    $this->response->title = __('MODULE_DEFAULT_NEWS');

    if ($action && $news_id)
      {
      $news_id = intval($news_id);
      if (!$this->news = $this->model_news->fetch($news_id))
        return $this->response->addInfo(__('NEWS_INFO_NOT_FOUND') . $news_id);
      }
    }

  public function _default()
    {
    if ($this->request->post('new_news'))
      {
      $this->newNews();
      }

    $this->listNews();
    }

  public function edit()
    {
    if (!$this->news)
      return;

    if ($this->request->post('edit_news'))
      {
      $this->editNews();
      }

    $data['news'] = $this->news;
    $this->view = new View('admin/edit-news', $data);
    }

  public function delete()
    {
    if (!$this->news)
      return;

    if ($this->request->post('delete_news_yes'))
      {
      $this->deleteNews();
      $this->listNews();
      }
    else if ($this->request->post('delete_news_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new View('admin/delete-news', array('news' => $this->news));
      }
    }

  private function listNews()
    {
    $data['news'] = $this->model_news->fetchAll();
    $data['new_news'] = $this->request->post('new_news');
    $this->view = new View('admin/list-news', $data);
    }

  private function newNews()
    {
    $errors = array();
    $title = $this->request->post('title');

    $this->news = new Knews($this->model_news);

    if (!$this->news->setTitle($title))
      $errors[] = __('NEWS_ERROR_TITLE_LENGTH');

    if (!count($errors))
      {
      $this->news->setContent('');
      $this->news->setCreator(Access::$user->id);
      $this->news->setCreated(time());
      $this->news->save();
      $this->response->redirect(urlModr($this->route, 'edit', $this->news->id));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function editNews()
    {
    $errors = array();
    $title = $this->request->post('title');
    $content = $this->request->post('content');

    if (!$this->news->setTitle($title))
      $errors[] = __('NEWS_ERROR_TITLE_LENGTH');

    if (!count($errors))
      {
      $this->news->setContent($content);
      $this->news->save();
      $this->response->addInfo(__('NEWS_INFO_SAVED'));
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function deleteNews()
    {
    $this->response->addInfo(__('NEWS_INFO_DELETED'));

    $this->news->delete();
    $this->news = false;
    }

  static function uninstall()
    {
    /*$files = array('../class/controller/AdminControllerNews.php');
    foreach ($files as $file)
      {
      if (file_exists($file))
        {
        unlink($file);
        }
      }*/
    return false;
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