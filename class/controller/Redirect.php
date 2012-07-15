<?php
/**
 * KWE Controller: Redirect
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.2
 */

class Redirect extends Controller
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