<?php
/**
 * KWE Model: ControllerModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.1
 */

class ControllerModel
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($id)
    {
    $q_select_controller = "SELECT * FROM `PREFIX_controllers` WHERE `id` = ?";

    $this->db->exec($q_select_controller, 'i', array($id));
    return $this->db->fetch('Kcontroller', array($this));
    }

  public function fetchByCName($class_name, $user)
    {
    $q_select_controller = "SELECT * FROM `PREFIX_controllers` LEFT JOIN "
      . "`PREFIX_controller_permissions` ON `controller` = `id`AND `user` = ? "
      . "WHERE `class_name` = ?";

    $this->db->exec($q_select_controller, 'is', array($user, $class_name));
    return $this->db->fetch('Kcontroller', array($this));
    }

  public function fetchAll()
    {
    $q_select_controllers = "SELECT * FROM `PREFIX_controllers` ORDER BY `name`";

    $this->db->exec($q_select_controllers);
    return $this->db->fetchAll();
    }

  public function fetchAllWithPermissions($user, $admin)
    {
    $join = ($admin ? 'LEFT' : 'INNER');
    $q_select_controllers = "SELECT * FROM `PREFIX_controllers` " . $join
      . " JOIN `PREFIX_controller_permissions` ON `controller` = `id` "
      . "AND `user` = ? ORDER BY `name`";

    $this->db->exec($q_select_controllers, 'i', array($user));
    return $this->db->fetchAll();
    }

  public function fetchFavorites($user)
    {
    $q_select_controllers = "SELECT * FROM `PREFIX_controllers` INNER JOIN "
      . "`PREFIX_controller_permissions` ON `controller` = `id` "
      . "AND `user` = ? ORDER BY `name`";

    $this->db->exec($q_select_controllers, 'i', array($user));
    return $this->db->fetchAll();
    }

  public function insert($query, $types, $values)
    {
    $q_insert_controller = "INSERT INTO `PREFIX_controllers` " . $query;
    $this->db->exec($q_insert_controller, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_controller = "UPDATE `PREFIX_controllers` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_controller, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_controller = "DELETE FROM `PREFIX_controllers` WHERE `id` = ?";
    return $this->db->exec($q_delete_controller, 'i', array($id));
    }
  }
?>