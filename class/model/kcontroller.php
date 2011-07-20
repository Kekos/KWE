<?php
/**
 * KWF Model: kcontroller
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-12
 * @version 1.0
 */

class kcontroller extends db_object
  {
  protected $id = null;
  protected $name = '';
  protected $class_name = '';
  protected $configurable = -1;
  protected $has_favorite_config = -1;

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

  public function setName($name)
    {
    if (strlen($name) > 1)
      {
      $this->_set('name', $name);
      return true;
      }

    return false;
    }

  public function setClassName($class_name)
    {
    if (strlen($class_name) > 1)
      {
      $this->_set('class_name', $class_name);
      return true;
      }

    return false;
    }

  public function setConfigurable($configurable)
    {
    $configurable = intval($configurable);
    if ($configurable === 0 || $configurable === 1)
      {
      $this->_set('configurable', $configurable);
      return true;
      }

    return false;
    }

  public function setHasFavoriteConfig($has_favorite_config)
    {
    $has_favorite_config = intval($has_favorite_config);
    if ($has_favorite_config === 0 || $has_favorite_config === 1)
      {
      $this->_set('has_favorite_config', $has_favorite_config);
      return true;
      }

    return false;
    }
  }
?>