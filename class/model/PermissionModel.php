<?php
/**
 * KWE Model: PermissionModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.1
 */

class PermissionModel
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($user, $page)
    {
    $q_select_permission = "SELECT `permission` FROM `PREFIX_permissions` "
      . "WHERE `user` = ? AND `page` = ?";

    $this->db->exec($q_select_permission, 'ii', array($user, $page));
    return $this->db->fetch();
    }

  public function fetchByUser($user)
    {
    $q_select_permissions = "SELECT p.`id`, p.`title`, p.`parent`, pe.* FROM "
      . "`PREFIX_pages` AS p LEFT JOIN `PREFIX_permissions`AS pe "
      . "ON p.`id` = pe.`page` AND pe.`user` = ? ORDER BY p.`url`";

    $this->db->exec($q_select_permissions, 'i', array($user));
    return $this->db->fetchAll();
    }

  public function insert($user, $page, $permission)
    {
    $q_insert_permission = "INSERT INTO `PREFIX_permissions` (`user`, `page`, "
      . "`permission`) VALUES (?, ?, ?)";

    return $this->db->exec($q_insert_permission, 'iii', array($user, $page, $permission));
    }

  public function update($user, $page, $permission)
    {
    $q_update_permission = "UPDATE `PREFIX_permissions` SET `permission` = ?x? "
      . "WHERE `user` = ? AND `page` = ?";

    return $this->db->exec($q_update_permission, 'ii', array($permission, $user, $page));
    }

  public function delete($user, $page)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_permissions` WHERE `user` = ? "
      . "AND `page` = ?";

    return $this->db->exec($q_delete_permission, 'ii', array($user, $page));
    }

  public function deleteByUser($user)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_permissions` WHERE `user` = ?";
    return $this->db->exec($q_delete_permission, 'i', array($user));
    }

  public function deleteByPage($page)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_permissions` WHERE `page` = ?";
    return $this->db->exec($q_delete_permission, 'i', array($page));
    }
  }
?>