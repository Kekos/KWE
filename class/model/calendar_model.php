<?php
/**
 * KWE Model: calendar_model
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-11
 * @version 1.1
 */

class calendar_model
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($id)
    {
    $q_select_event = "SELECT * FROM `PREFIX_calendar` WHERE `id` = ?";

    $this->db->exec($q_select_event, 'i', array($id));
    return $this->db->fetch('kevent', array($this));
    }

  public function fetchAll()
    {
    $q_select_events = "SELECT `id`, `title`, `starttime`, `endtime` FROM "
      . "`PREFIX_calendar` ORDER BY `id` DESC";

    $this->db->exec($q_select_events);
    return $this->db->fetchAll();
    }

  public function fetchTimespan($time, $limit)
    {
    $q_select_events = "SELECT * FROM `PREFIX_calendar` WHERE "
      . "`endtime` > ? ORDER BY `starttime` LIMIT 0, ?";

    $this->db->exec($q_select_events, 'ii', array($time, $limit));
    return $this->db->fetchAll();
    }

  public function fetchAllFull($order, $start = 0, $limit = 0)
    {
    $q_select_news = "SELECT n.*, n.`creator` AS `creator_id`, u.`name` AS `creator`"
     .  " FROM `PREFIX_calendar` AS n INNER JOIN `PREFIX_users` AS u"
     . " ON u.`id` = n.`creator` ORDER BY `id` " . $order;
    if ($limit)
      $q_select_news .= " LIMIT " . $start . ", " . $limit;

    $this->db->exec($q_select_news);
    return $this->db->fetchAll();
    }

  public function insert($query, $types, $values)
    {
    $q_insert_event = "INSERT INTO `PREFIX_calendar` " . $query;
    $this->db->exec($q_insert_event, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_event = "UPDATE `PREFIX_calendar` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_event, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_event = "DELETE FROM `PREFIX_calendar` WHERE `id` = ?";
    return $this->db->exec($q_delete_event, 'i', array($id));
    }
  }
?>