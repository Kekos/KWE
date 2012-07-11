<?php
/**
 * KWF Model: Knews
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.1
 */

class Knews extends DbObject
  {
  protected $id = null;
  protected $title = '';
  protected $content = null;
  protected $creator = -1;
  protected $created = -1;

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

  public function setContent($content)
    {
    $this->_set('content', $content);
    return true;
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
  }
?>