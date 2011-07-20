<?php
/**
 * KWF Model: page_controller
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-12
 * @version 1.0
 */

class page_controller extends db_object
  {
  protected $id = null;
  protected $page = -1;
  protected $controller = null;
  protected $content = null;
  protected $order = -1;

  public function __construct($model)
    {
    $this->_model = $model;
    }

  public function save()
    {
    if ($this->id == null)
      {
      $this->id = $this->_model->insert($this->_insertQuery(), $this->_getTypes(), $this->_getEdited());
      }
    else if (count($this->_edited))
      {
      $this->_model->update($this->_updateQuery(), $this->_getTypes(), $this->_getEdited(), $this->id);
      }

    $this->_restoreEdited();
    }

  public function delete()
    {
    if ($this->id != null)
      {
      $this->_model->delete($this->id);
      }
    }

  public function setPage($page)
    {
    $this->_set('page', intval($page));
    return true;
    }

  public function setController($controller)
    {
    $this->_set('controller', intval($controller));
    return true;
    }

  public function setContent($content)
    {
    $this->_set('content', $content);
    return true;
    }

  public function setOrder($order)
    {
    $this->_set('order', intval($order));
    return true;
    }
  }
?>