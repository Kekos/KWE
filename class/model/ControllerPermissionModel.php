<?php
/**
 * KWE Model: ControllerPermissionModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.1
 */

class ControllerPermissionModel
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($user, $controller_cname)
    {
    $q_select_permission = "SELECT * FROM `PREFIX_controller_permissions` "
      . "INNER JOIN `PREFIX_controllers` ON `id` = `controller` WHERE "
      . "`user` = ? AND `class_name` = ?";

    $this->db->exec($q_select_permission, 'is', array($user, $controller_cname));
    return $this->db->fetch();
    }

  public function insert($user, $controller, $favorite = 0)
    {
    $q_insert_permission = "INSERT INTO `PREFIX_controller_permissions` ("
      . "`user`, `controller`, `favorite`) VALUES (?, ?, ?)";

    return $this->db->exec($q_insert_permission, 'iii', array($user, $controller, 
        $favorite));
    }

  public function update($favorite, $user, $controller)
    {
    $q_update_permission = "UPDATE `PREFIX_controller_permissions` SET "
      . "`favorite` = ? WHERE `user` = ? AND `controller` = ?";

    return $this->db->exec($q_update_permission, 'iii', array($favorite, $user, 
        $controller));
    }

  public function delete($user, $controller)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_controller_permissions` WHERE "
      . "`user` = ? AND `controller` = ?";

    return $this->db->exec($q_delete_permission, 'ii', array($user, $controller));
    }

  public function deleteByUser($user)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_controller_permissions` WHERE "
      . "`user` = ?";

    return $this->db->exec($q_delete_permission, 'i', array($user));
    }

  public function deleteByController($controller)
    {
    $q_delete_permission = "DELETE FROM `PREFIX_controller_permissions` WHERE "
      . "`controller` = ?";

    return $this->db->exec($q_delete_permission, 'i', array($controller));
    }
  }
?>