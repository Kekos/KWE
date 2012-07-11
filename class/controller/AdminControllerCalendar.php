<?php
/**
 * KWF Controller: AdminControllerCalendar
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.2
 */

class AdminControllerCalendar extends Controller
  {
  private $db = null;
  private $model_calendar = null;
  private $event = false;

  public function before($action = false, $event_id = false)
    {
    if (!Access::$is_logged_in || !Access::$is_administrator || !Access::hasControllerPermission('calendar'))
      {
      $this->response->redirect(urlModr());
      }

    $this->db = DbMysqli::getInstance();
    $this->model_calendar = new CalendarModel($this->db);
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
    $this->view = new View('admin/edit-event', $data);
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
      $this->view = new View('admin/delete-event', array('event' => $this->event));
      }
    }

  private function listEvents()
    {
    $data['events'] = $this->model_calendar->fetchAll();
    $data['new_event'] = $this->request->post('new_event');
    $this->view = new View('admin/list-events', $data);
    }

  private function newEvent()
    {
    $errors = array();
    $title = $this->request->post('title');
    $content = $this->request->post('content');
    $starttime = $this->request->post('starttime');
    $endtime = $this->request->post('endtime');

    $this->event = new Kevent($this->model_calendar);

    if (!$this->event->setTitle($title))
      $errors[] = 'Du måste ange en titel på minst 2 tecken.';
    if (!$this->event->setStarttime($starttime))
      $errors[] = 'Du angav inte ett korrekt startdatum.';
    if (!$this->event->setEndtime($endtime))
      $errors[] = 'Du angav inte ett korrekt slutdatum.';

    if (!count($errors))
      {
      $this->event->setContent($content);
      $this->event->setCreator(Access::$user->id);
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
    $db = DbMysqli::getInstance();
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
    $files = array('../class/controller/AdminControllerCalendar.php', 
        '../class/controller/Calendar.php', 
        '../class/model/CalendarModel.php', 
        '../class/model/Kcalendar.php', 
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

    $db = DbMysqli::getInstance();
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