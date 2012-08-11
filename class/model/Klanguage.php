<?php
/**
 * KWF Model: Klanguage
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-09
 * @version 1.0
 */

class Klanguage extends DbObject
  {
  protected $id = null;
  protected $name = null;
  protected $code = null;

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

    $this->_restoreEdited();
    }

  public function delete()
    {
    if ($this->id != null)
      {
      $this->_model->delete($this->id);
      }
    }

  public function setName($name)
    {
    if (strlen($name) > 1)
      {
      $this->_set('name', $name);
      return true;
      }

    return false;
    }

  public function setCode($code)
    {
    if (strlen($code) > 0 && strlen($code) < 5)
      {
      $this->_set('code', $code);
      return true;
      }

    return false;
    }
  }
?>