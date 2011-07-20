<?php
/**
 * KWE Controller: admin_updates
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 2.1
 */

class admin_updates extends controller
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
    if ($update_obj->status->code == 0)
      {
      $data['update_status'] = 0;
      }
    else
      {
      $data['update_status'] = 1;
      $data['update_link'] = $update_obj->package;
      }

    $this->view = new view('admin/update', $data);
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