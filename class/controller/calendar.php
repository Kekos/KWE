<?php
/**
 * KWE Controller: calendar
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 2.1
 */

class calendar extends controller
  {
  private $db;
  private $model_calendar = null;
  private $settings = null;

  public function _default($event_id = false)
    {
    $this->db = db_mysqli::getInstance();
    $this->model_calendar = new calendar_model($this->db);
    $this->settings = json_decode($this->controller_data->content);

    $data['settings'] = $this->settings;

    if (is_numeric($event_id))
      {
      $data['event'] = $this->model_calendar->fetch($this->request->params[2]);
      }
    else
      {
      $data['events'] = $this->model_calendar->fetchTimespan(strtotime('today 00:00'), $this->settings->num_events);
      }

    $this->view = new view('calendar', $data);
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