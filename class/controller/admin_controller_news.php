<?php
/**
 * KWF Controller: admin_controller_news
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-18
 * @version 2.2
 */

class admin_controller_news extends controller
  {
  private $db = null;
  private $model_news = null;
  private $news = false;

  public function before($action = false, $news_id = false)
    {
    if (!access::$is_logged_in || !access::$is_administrator || !access::hasControllerPermission('news'))
      {
      $this->response->redirect(urlModr());
      }

    $this->db = db_mysqli::getInstance();
    $this->model_news = new news_model($this->db);
    $this->response->title = 'Nyheter';

    if ($action && $news_id)
      {
      $news_id = intval($news_id);
      if (!$this->news = $this->model_news->fetch($news_id))
        return $this->response->addInfo('Hittade inte nyheten med ID ' . $news_id);
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
    $this->view = new view('admin/edit-news', $data);
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
      $this->view = new view('admin/delete-news', array('news' => $this->news));
      }
    }

  private function listNews()
    {
    $data['news'] = $this->model_news->fetchAll();
    $data['new_news'] = $this->request->post('new_news');
    $this->view = new view('admin/list-news', $data);
    }

  private function newNews()
    {
    $errors = array();
    $title = $this->request->post('title');

    $this->news = new knews($this->model_news);

    if (!$this->news->setTitle($title))
      $errors[] = 'Du måste ange en nyhetstitel på minst 2 tecken.';

    if (!count($errors))
      {
      $this->news->setContent('');
      $this->news->setCreator(access::$user->id);
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
      $errors[] = 'Du måste ange en nyhetstitel på minst 2 tecken.';

    if (!count($errors))
      {
      $this->news->setContent($content);
      $this->news->save();
      $this->response->addInfo('Nyheten sparades.');
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function deleteNews()
    {
    $this->response->addInfo('Nyheten togs bort.');

    $this->news->delete();
    $this->news = false;
    }

  static function uninstall()
    {
    /*$files = array('../class/controller/admin_controller_news.php');
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