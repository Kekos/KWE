<?php
/**
 * KWF Controller: admin_controller_calendar
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-18
 * @version 2.2
 */

class admin_controller_calendar extends controller
  {
  private $db = null;
  private $model_calendar = null;
  private $event = false;

  public function before($action = false, $event_id = false)
    {
    if (!access::$is_logged_in || !access::$is_administrator || !access::hasControllerPermission('calendar'))
      {
      $this->response->redirect(urlModr());
      }

    $this->db = db_mysqli::getInstance();
    $this->model_calendar = new calendar_model($this->db);
    $this->response->title = 'Kalender';

    if ($action && $event_id)
      {
      $event_id = intval($event_id);
      if (!$this->event = $this->model_calendar->fetch($event_id))
        return $this->response->addInfo('Hittade inte händelsen med ID ' . $event_id);
      }
    }

  public function _default()
    {
    if ($this->request->post('new_event'))
      {
      $this->newEvent();
      }

    $this->listEvents();
    }

  public function edit()
    {
    if (!$this->event)
      return;

    if ($this->request->post('edit_event'))
      {
      $this->editEvent();
      }

    $data['event'] = $this->event;
    $this->view = new view('admin/edit-event', $data);
    }

  public function delete()
    {
    if (!$this->event)
      return;

    if ($this->request->post('delete_event_yes'))
      {
      $this->deleteEvent();
      $this->listEvents();
      }
    else if ($this->request->post('delete_event_no'))
      {
      $this->response->redirect(urlModr($this->route));
      }
    else
      {
      $this->view = new view('admin/delete-event', array('event' => $this->event));
      }
    }

  private function listEvents()
    {
    $data['events'] = $this->model_calendar->fetchAll();
    $data['new_event'] = $this->request->post('new_event');
    $this->view = new view('admin/list-events', $data);
    }

  private function newEvent()
    {
    $errors = array();
    $title = $this->request->post('title');
    $content = $this->request->post('content');
    $starttime = $this->request->post('starttime');
    $endtime = $this->request->post('endtime');

    $this->event = new kevent($this->model_calendar);

    if (!$this->event->setTitle($title))
      $errors[] = 'Du måste ange en titel på minst 2 tecken.';
    if (!$this->event->setStarttime($starttime))
      $errors[] = 'Du angav inte ett korrekt startdatum.';
    if (!$this->event->setEndtime($endtime))
      $errors[] = 'Du angav inte ett korrekt slutdatum.';

    if (!count($errors))
      {
      $this->event->setContent($content);
      $this->event->setCreator(access::$user->id);
      $this->event->setCreated(time());
      $this->event->save();
      $this->response->addInfo('Händelsen ' . htmlspecialchars($title) . ' sparades.');
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function editEvent()
    {
    $errors = array();
    $title = $this->request->post('title');
    $content = $this->request->post('content');
    $starttime = $this->request->post('starttime');
    $endtime = $this->request->post('endtime');

    if (!$this->event->setTitle($title))
      $errors[] = 'Du måste ange en titel på minst 2 tecken.';
    if (!$this->event->setStarttime($starttime))
      $errors[] = 'Du angav inte ett korrekt startdatum.';
    if (!$this->event->setEndtime($endtime))
      $errors[] = 'Du angav inte ett korrekt slutdatum.';

    if (!count($errors))
      {
      $this->event->setContent($content);
      $this->event->save();
      $this->response->addInfo('Händelsen ' . htmlspecialchars($title) . ' sparades.');
      }
    else
      {
      $this->response->addError($errors);
      }
    }

  private function deleteEvent()
    {
    $this->response->addInfo('Händelsen ' . htmlspecialchars($this->event->title) . ' togs bort.');

    $this->event->delete();
    $this->event = false;
    }

  static function install()
    {
    $db = db_mysqli::getInstance();
    $db->exec("CREATE TABLE `PREFIX_calendar` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `title` varchar(30) collate utf8_unicode_ci NOT NULL,
  `content` text collate utf8_unicode_ci NOT NULL,
  `starttime` int(10) unsigned NOT NULL,
  `endtime` int(10) unsigned NOT NULL,
  `creator` smallint(5) unsigned NOT NULL,
  `created` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;");
    }

  static function uninstall()
    {
    $files = array('../class/controller/admin_controller_calendar.php', 
        '../class/controller/calendar.php', 
        '../class/model/calendar_model.php', 
        '../class/model/kcalendar.php', 
        '../view/calendar.phtml', 
        '../view/admin/delete-event.phtml', 
        '../view/admin/edit-event.phtml', 
        '../view/admin/list-events.phtml', 
        '../view/admin/controller.page.calendar.phtml');
    foreach ($files as $file)
      {
      if (file_exists($file))
        {
        unlink($file);
        }
      }

    $db = db_mysqli::getInstance();
    $db->exec("DROP TABLE `PREFIX_calendar`");

    return true;
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