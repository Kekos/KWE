<?php
/**
 * KWE Controller: admin_index
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-13
 * @version 2.1
 */

class admin_index extends controller
  {
  private $db;
  private $model_page = null;
  private $model_controller = null;

  public function _default()
    {
    $this->db = db_mysqli::getInstance();
    $this->model_page = new page_model($this->db);
    $this->model_controller = new controller_model($this->db);

  if (!access::$is_logged_in || !access::$is_administrator)
      {
      $this->response->title = 'Logga in';
      return;
      }

    $data['last_pages'] = $this->model_page->fetchLastEdited();
    $data['controllers'] = $this->model_controller->fetchFavorites(access::$user->id);
    $this->view = new view('admin/index', $data);
    }

  public function run()
    {
    if ($this->view != null)
      {
      $this->response->setContentType('html');
      $this->response->addContent($this->view->compile());
      }
    }
  }
?>