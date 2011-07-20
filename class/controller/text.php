<?php
/**
 * KWF Controller: text
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 2.1
 */

class text extends controller
  {
  public function _default()
    {
    $data['content'] = $this->controller_data->content;
    $this->view = new view('text', $data);
    }

  public function run()
    {
    if ($this->view != null)
      {
      $this->response->setContentType('html'); // The controller MUST set the content type
      $this->response->addContent($this->view->compile());
      }
    }
  }
?>