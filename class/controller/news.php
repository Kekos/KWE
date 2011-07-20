<?php
/**
 * KWE Controller: news
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 2.1
 */

class news extends controller
  {
  private $db;
  private $model_news = null;
  private $settings = null;

  public function _default($news_id = false)
    {
    $this->db = db_mysqli::getInstance();
    $this->model_news = new news_model($this->db);
    $this->settings = json_decode($this->controller_data->content);

    $data['settings'] = $this->settings;

    if (is_numeric($news_id))
      {
      $data['news'] = array($this->model_news->fetch($news_id));
      }
    else
      {
      $data['news'] = $this->model_news->fetchAllFull($this->settings->order, 
          0, $this->settings->num_news);
      }

    $this->view = new view('news', $data);
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