<?php
/**
 * KWE Controller: redirect
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-06-19
 * @version 2.2
 */

class redirect extends Controller
  {
  public function _default()
    {
    $this->response->redirect($this->controller_data->content);
    }

  public function run()
    {
    }
  }
?>