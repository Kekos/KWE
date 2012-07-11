<?php
/**
 * KWE Controller: AdminIndex
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.2
 */

class AdminIndex extends Controller
  {
  private $db;
  private $model_page = null;
  private $model_controller = null;

  public function _default()
    {
    $this->db = DbMysqli::getInstance();
    $this->model_page = new PageModel($this->db);
    $this->model_controller = new ControllerModel($this->db);

    if (!Access::$is_logged_in || !Access::$is_administrator)
      {
      $this->response->title = 'Logga in';
      return;
      }

    if (Access::$is_administrator)
      {
      $update_session = $this->request->session->get('update');
      if (!$update_session)
        {
        $update_obj = @file_get_remote_contents('http://kekos.se/kwe/update/' . KWE_VERSION . '/');
        if (!empty($update_obj))
          {
          $update_session = json_decode($update_obj);
          }

        $this->request->session->set('update', $update_session);
        }

      if (is_object($update_session) && $update_session->status->code == 1)
        {
        $this->response->addInfo('Det finns en uppdatering till KWE att hämta! Installera den nya versionen nu för att se till att ditt KWE är säkert. <a href="' . htmlspecialchars($update_session->package) . '">Klicka här</a>');
        }
      }

    $data['last_pages'] = $this->model_page->fetchLastEdited();
    $data['controllers'] = $this->model_controller->fetchFavorites(Access::$user->id);
    $this->view = new View('admin/index', $data);
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