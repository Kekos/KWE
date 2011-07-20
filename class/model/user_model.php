<?php
/**
 * KWF Model: user_model
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-05-20
 * @version 2.1
 */

class user_model
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function login($username, $password)
    {
    $q_select_user = "SELECT `id`, `online_time` FROM `PREFIX_users` WHERE "
      . "`username` = ? AND `password` = ?";

    $this->db->exec($q_select_user, 'ss', array($username, $password));
    return $this->db->fetch('user', array($this));
    }

  public function fetch($id)
    {
    $q_select_user = "SELECT `id`, `name`, `username`, `password`, `rank` FROM "
      . "`PREFIX_users` WHERE `id` = ?";

    $this->db->exec($q_select_user, 'i', array($id));
    return $this->db->fetch('user', array($this));
    }

  public function availableUsername($username)
    {
    $q_select_user = "SELECT `id` FROM `PREFIX_users` WHERE `username` = ?";

    $this->db->exec($q_select_user, 's', array($username));
    return ($this->db->fetch() ? 0 : 1);
    }

  public function fetchAll()
    {
    $q_select_users = "SELECT `id`, `name`, `username`, `rank`, `online_time` "
      . "FROM `PREFIX_users` ORDER BY `id`";

    $this->db->exec($q_select_users);
    return $this->db->fetchAll();
    }

  public function clearOnlineCache($time)
    {
    $q_update_users = "UPDATE `PREFIX_users` SET `online`= 0 WHERE `online` = 1 "
      . "AND `online_time` < ?";

    return $this->db->exec($q_update_users, 'i', array($time + 180));
    }

  public function insert($query, $types, $values)
    {
    $q_insert_user = "INSERT INTO `PREFIX_users` " . $query;
    $this->db->exec($q_insert_user, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_user = "UPDATE `PREFIX_users` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_user, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_user = "DELETE FROM `PREFIX_users` WHERE `id` = ?";
    return $this->db->exec($q_delete_user, 'i', array($id));
    }
  }
?>