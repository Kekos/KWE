<?php
/**
 * KWE Model: PageControllerModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 2.0
 */

class PageControllerModel
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($id)
    {
    $q_select_controller = "SELECT p.*, `name`, `class_name` FROM "
      . "`PREFIX_page_controllers` AS p INNER JOIN `PREFIX_controllers` AS c "
      . "ON c.`id` = `controller` WHERE p.`id` = ?";

    $this->db->exec($q_select_controller, 'i', array($id));
    return $this->db->fetch('PageController', array($this));
    }

  public function fetchByOrder($order, $gl, $dir, $args)
    {
    $q_select_controller = "SELECT * FROM `PREFIX_page_controllers` WHERE `page` = ? "
      . "AND `id` != ? AND `order` " . $gl . " ? ORDER BY `order` "
      . $dir . " LIMIT 1";

    $args[] = $order;
    $this->db->exec($q_select_controller, 'iii', $args);
    return $this->db->fetch('PageController', array($this));
    }

  public function fetchAll($page)
    {
    $q_select_controllers = "SELECT p.id, c.`name`, `class_name` FROM `PREFIX_page_controllers` "
      . "AS p INNER JOIN `PREFIX_controllers` AS c ON c.`id` = `controller` "
      . "WHERE `page` = ? ORDER BY `order` ASC";

    $this->db->exec($q_select_controllers, 'i', array($page));
    return $this->db->fetchAll();
    }

  public function insert($query, $types, $values)
    {
    $q_insert_controller = "INSERT INTO `PREFIX_page_controllers` " . $query;
    $this->db->exec($q_insert_controller, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_controller = "UPDATE `PREFIX_page_controllers` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_controller, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_controller = "DELETE FROM `PREFIX_page_controllers` WHERE `id` = ?";
    return $this->db->exec($q_delete_controller, 'i', array($id));
    }

  public function deleteByPage($page)
    {
    $q_delete_controller = "DELETE FROM `PREFIX_page_controllers` WHERE `page` = ?";
    return $this->db->exec($q_delete_controller, 'i', array($page));
    }
  }
?>