<?php
/**
 * KWE Controller: News
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.2
 */

class News extends Controller
  {
  private $db;
  private $model_news = null;
  private $settings = null;

  public function _default($news_id = false)
    {
    $this->db = DbMysqli::getInstance();
    $this->model_news = new NewsModel($this->db);
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

    $this->view = new View('news', $data);
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