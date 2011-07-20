<?php
/**
 * KWE Model: news_model
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 1.1
 */

class news_model
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($id)
    {
    $q_select_news = "SELECT n.*, n.`creator` AS `creator_id`, u.`name` AS `creator` "
      . "FROM `PREFIX_news` AS n INNER JOIN `PREFIX_users` AS u "
      . "ON u.`id` = n.`creator` WHERE n.`id` = ?";

    $this->db->exec($q_select_news, 'i', array($id));
    return $this->db->fetch('knews', array($this));
    }

  public function fetchAll()
    {
    $q_select_news = "SELECT `id`, `title`, `created` FROM `PREFIX_news` "
      . "ORDER BY `id` DESC";

    $this->db->exec($q_select_news);
    return $this->db->fetchAll();
    }

  public function fetchAllFull($order, $start = 0, $limit = 0)
    {
    $q_select_news = "SELECT n.*, n.`creator` AS `creator_id`, u.`name` AS `creator` "
      . "FROM `PREFIX_news` AS n INNER JOIN `PREFIX_users` AS u "
      . "ON u.`id` = n.`creator` ORDER BY n.`id` " . $order;
    if ($limit)
      $q_select_news .= " LIMIT " . $start . ", " . $limit;

    $this->db->exec($q_select_news);
    return $this->db->fetchAll();
    }

  public function insert($query, $types, $values)
    {
    $q_insert_news = "INSERT INTO `PREFIX_news` " . $query;
    $this->db->exec($q_insert_news, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_news = "UPDATE `PREFIX_news` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_news, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_news = "DELETE FROM `PREFIX_news` WHERE `id` = ?";
    return $this->db->exec($q_delete_news, 'i', array($id));
    }
  }
?>