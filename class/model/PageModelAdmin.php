<?php
/**
 * KWF Model: PageModelAdmin
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.0
 */

class PageModelAdmin
  {
  private $page = array();
  private $controllers = array();

  public function addController($controller, $content)
    {
    $obj = new stdclass();
    $obj->name = $controller;
    $obj->content = $content;
    $this->controllers[] = $obj;
    }

  public function getPage($page_name)
    {
    $page_file = BASE . 'admin/pages/' . $page_name . '.php';
    if (!file_exists($page_file))
      {
      if (file_exists(BASE . 'class/controller/AdminController' . $page_name . '.php'))
        {
        $page_file = BASE . 'admin/pages/controller_wrapper.php';
        }
      else
        return false;
      }
    require($page_file);

    return $this->page;
    }

  public function getControllers()
    {
    return $this->controllers;
    }
  }
?>