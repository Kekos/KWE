<?php
/**
 * KWF Controller: Text
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.2
 */

class Text extends Controller
  {
  public function _default()
    {
    $data['content'] = $this->controller_data->content;
    $this->view = new View('text', $data);
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