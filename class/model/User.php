<?php
/**
 * KWF Model: User
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.3
 */

class User extends DbObject
  {
  protected $id = null;
  protected $name = '';
  protected $username = '';
  protected $password = '';
  protected $rank = 0;
  protected $online = -1;
  protected $online_time = -1;

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
    if (strlen($name) > 3)
      {
      $this->_set('name', $name);
      return true;
      }

    return false;
    }

  public function setUsername($username)
    {
    if (strlen($username) > 1 && $this->_model->availableUsername($username))
      {
      $this->_set('username', $username);
      return true;
      }

    return false;
    }

  public function setPassword($password)
    {
    if (strlen($password) > 5)
      {
      $this->_set('password', md5($password));
      return true;
      }

    return false;
    }

  public function setRank($rank)
    {
    if ($rank > 0 || $rank < 3)
      {
      $this->_set('rank', $rank);
      return true;
      }

    return false;
    }

  public function setOnline($online)
    {
    if ($online === 0 || $online === 1)
      {
      $this->_set('online', $online);
      return true;
      }

    return false;
    }

  public function setOnlineTime()
    {
    $this->_set('online_time', time());
    return true;
    }
  }
?>