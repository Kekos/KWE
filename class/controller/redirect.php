<?php
/**
 * KWE Controller: redirect
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 2.1
 */

class redirect extends controller
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