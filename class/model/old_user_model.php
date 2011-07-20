<?php
/**
 * KWE Model: user_model
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @version 1.0
 */

class user_model
  {
  private $db = null;

  static $user = null;
  static $is_logged_in = false;

  public function __construct($db, $user_id_logged_in = false)
    {
    $this->db = $db;

    if ($user_id_logged_in != false)
      {
      if (self::$user = $this->fetch($user_id_logged_in))
        {
        self::$is_logged_in = true;
        $this->updateOnlinetime(time(), $user_id_logged_in);
        }
      }
    }

  public function login($username, $password, $admin = 0)
    {
    $q_select_user = "SELECT `id`, `online_time` FROM `PREFIX_users` WHERE ";
    $q_select_user .= "`username` = '?x?' AND `password` = '?x?'";
    if ($admin)
      $q_select_user .= " AND `rank` < 3";

    $this->db->query($q_select_user, array($username, $password));
    return $this->db->fetch();
    }

  public function fetch($id)
    {
    $q_select_user = "SELECT `id`, `name`, `username`, `password`, `rank` FROM ";
    $q_select_user .= "`PREFIX_users` WHERE `id` = ?x?";

    $this->db->query($q_select_user, array($id));
    return $this->db->fetch();
    }

  public function fetchUserPerms($id)
    {
    $q_select_perms = "SELECT p.`id`, p.`title`, pe.`page` AS ch FROM `PREFIX_pages` AS p LEFT JOIN `PREFIX_perm` AS pe";
    $q_select_perms .= "  ON p.`id` = pe.`page` AND pe.`user` = ?x? ORDER BY p.`site`";

    $this->db->query($q_select_perms, array($id));
    return $this->db->fetchAll();
    }

  public function fetchAll()
    {
    $q_select_users = "SELECT `id`, `name`, `username`, `rank`, `online_time` ";
    $q_select_users .= "FROM `PREFIX_users` ORDER BY `id`";

    $this->db->query($q_select_users);
    return $this->db->fetchAll();
    }

  public function updateOnline($online, $id)
    {
    $q_update_user = "UPDATE `PREFIX_users` SET `online`= ?x? WHERE `id` = ?x?";

    return $this->db->query($q_update_user, array($online, $id));
    }

  public function updateOnlinetime($time, $id)
    {
    $q_update_user = "UPDATE `PREFIX_users` SET `online`= 1, `online_time` = ";
    $q_update_user .= "?x? WHERE `id` = ?x?";

    return $this->db->query($q_update_user, array($time, $id));
    }

  public function clearOnlineCache($time)
    {
    $q_update_users = "UPDATE `PREFIX_users` SET `online`= 0 WHERE `online` = 1 ";
    $q_update_users .= "AND `online_time` < (?x? + 180)";

    return $this->db->query($q_update_users, array($time));
    }

  public function insert($name, $username, $password, $rank)
    {
    $q_insert_user = "INSERT INTO `PREFIX_users` (`name`, `username`, `password`";
    $q_insert_user .= ", `rank`, `online`, `online_time`) VALUES ('?x?', '?x?', '?x?', ?x?, 0, 0)";

    return $this->db->query($q_insert_user, array($name, $username, $password, $rank));
    }

  public function insertPerm($user, $page)
    {
    $q_insert_perm = "INSERT INTO `PREFIX_perm` (`user`, `page`) VALUES (?x?, ?x?) ON DUPLICATE KEY UPDATE `page` = ?x?";

    return $this->db->query($q_insert_perm, array($user, $page, $page));
    }

  public function update($name, $rank, $id)
    {
    $q_update_user = "UPDATE `PREFIX_users` SET `name` = '?x?', `rank` = ?x? ";
    $q_update_user .= "WHERE `id` = ?x?";

    return $this->db->query($q_update_user, array($name, $rank, $id));
    }

  public function updatePassword($password, $id)
    {
    $q_update_user = "UPDATE `PREFIX_users` SET `password` = '?x?' WHERE `id` = ?x?";
    return $this->db->query($q_update_user, array($password, $id));
    }

  public function delete($id)
    {
    $q_delete_user = "DELETE FROM `PREFIX_users` WHERE `id` = ?x?";
    return $this->db->query($q_delete_user, array($id));
    }

  public function deletePerm($user, $page = 0)
    {
    $q_delete_perm = "DELETE FROM `PREFIX_perm` WHERE `user` = ?x?";
    if ($page)
      $q_delete_perm .= " AND `page` NOT IN (" . $page . ")";
    return $this->db->query($q_delete_perm, array($user));
    }
  }
?>