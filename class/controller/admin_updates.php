<?php
/**
 * KWE Controller: admin_updates
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-06-19
 * @version 2.1
 */

class admin_updates extends Controller
  {
  public function _default()
    {
    if (!access::$is_logged_in || !access::$is_administrator)
      {
      $this->response->redirect(urlModr());
      }

    $update_obj = file_get_remote_contents('http://kekos.se/kwe/update/' . KWE_VERSION . '/');
    if (empty($update_obj))
      return $this->response->addError('Det gick inte att ansluta till uppdateringsservern just nu. Försök igen senare.');

    $update_obj = json_decode($update_obj);
    $data['update_status'] = $update_obj->status->code;

    if ($update_obj->status->code == 1)
      {
      $data['update_link'] = $update_obj->package;
      }
    else if ($update_obj->status->code == 2)
      {
      $this->response->addError('Det gick inte att få paketinformation från servern.');
      }

    $this->view = new View('admin/update', $data);
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