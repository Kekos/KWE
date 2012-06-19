<?php
/**
 * KWF Model: kpage
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-06-19
 * @version 1.1
 */

class kpage extends DbObject
  {
  protected $id = null;
  protected $title = '';
  protected $url = '';
  protected $parent = -1;
  protected $public = -1;
  protected $show_in_menu = -1;
  protected $order = -1;
  protected $creator = -1;
  protected $created = -1;
  protected $editor = -1;
  protected $edited = -1;

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

  public function setTitle($title)
    {
    if (strlen($title) > 1)
      {
      $this->_set('title', $title);
      return true;
      }

    return false;
    }

  public function setUrl($url)
    {
    if (strlen($url) > 1)
      {
      $this->_set('url', $url);
      return true;
      }

    return false;
    }

  public function setParent($parent)
    {
    $this->_set('parent', intval($parent));
    return true;
    }

  public function setPublic($public)
    {
    $public = intval($public);
    if ($public === 0 || $public === 1)
      {
      $this->_set('public', $public);
      return true;
      }

    return false;
    }

  public function setShowInMenu($show_in_menu)
    {
    $show_in_menu = intval($show_in_menu);
    if ($show_in_menu === 0 || $show_in_menu === 1)
      {
      $this->_set('show_in_menu', $show_in_menu);
      return true;
      }

    return false;
    }

  public function setOrder($order)
    {
    $order = intval($order);
    if ($order >= 0)
      {
      $this->_set('order', $order);
      return true;
      }

    return false;
    }

  public function setCreator($creator)
    {
    $this->_set('creator', intval($creator));
    return true;
    }

  public function setCreated($created)
    {
    $this->_set('created', intval($created));
    return true;
    }

  public function setEditor($editor)
    {
    $this->_set('editor', intval($editor));
    return true;
    }

  public function setEdited($edited)
    {
    $this->_set('edited', intval($edited));
    return true;
    }
  }
?>