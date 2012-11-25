<?php
/**
 * KWE Model: PageModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-11-25
 * @version 2.1
 */

class PageModel
  {
  private $db = null;
  private $active_page = false;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function getPage($page_name)
    {
    $q_select_page = "SELECT * FROM `PREFIX_pages` WHERE `url` = ? AND `public` = 1";
    $this->db->exec($q_select_page, 's', array($page_name));
    $this->active_page = $this->db->fetch('Kpage', array($this));
    return $this->active_page;
    }

  public function fetchPagePermission($page_name, $user)
    {
    $q_select_page = "SELECT p.*, pe.`permission` FROM `PREFIX_pages` AS p LEFT "
      . "JOIN `PREFIX_permissions` AS pe ON p.`id` = pe.`page` "
      . "AND pe.`user` = ? WHERE p.`url` = ?";

    $this->db->exec($q_select_page, 'is', array($user, $page_name));
    return $this->db->fetch('Kpage', array($this));
    }

  public function getControllers($page_name)
    {
    $q_select_controllers = "SELECT c.`class_name` AS name, pc.`content` FROM "
      . "`PREFIX_page_controllers` AS pc INNER JOIN `PREFIX_controllers` AS c "
      . "ON c.`id` = `controller` WHERE pc.`page` = ? ORDER BY pc.`order` ASC";

    $this->db->exec($q_select_controllers, 'i', array($this->active_page->id));
    return $this->db->fetchAll();
    }

  public function fetchPageList($show_in_menu = 1, $extended = 0, $language)
    {
    $cols = ($extended ? ', `public`, `show_in_menu`, `order`, `edited`' : '');
    $q_select_pages = "SELECT `title`, `url`" . $cols . " FROM `PREFIX_pages` WHERE `parent` = 0";
    if ($show_in_menu)
      $q_select_pages .= " AND `show_in_menu` = 1 AND `public` = 1";
    $q_select_pages .= " AND `language` = ? ORDER BY `order`";

    $this->db->exec($q_select_pages, 'i', array($language));
    return $this->db->fetchAll();
    }

  public function fetchSubPageList($page, $show_in_menu = 1, $extended = 0)
    {
    $cols = ($extended ? ', `public`, `show_in_menu`, `order`, `edited`' : '');
    $q_select_pages = "SELECT `id`, `title`, `url`" . $cols . " FROM `PREFIX_pages` WHERE "
      . "(`parent` = ? OR `id` = ?)";
    if ($show_in_menu)
      $q_select_pages .= " AND `show_in_menu` = 1 AND `public` = 1";
    $q_select_pages .= " ORDER BY `parent`, `order`";

    $this->db->exec($q_select_pages, 'ii', array($page, $page));
    return $this->db->fetchAll();
    }

  public function fetchLastEdited()
    {
    $q_select_pages = "SELECT p.`title`, p.`url`, p.`edited`, u.`name` FROM"
      . " `PREFIX_pages` AS p INNER JOIN `PREFIX_users` AS u ON "
      . " u.`id` = p.`editor` ORDER BY `edited` DESC LIMIT 0, 10";

    $this->db->exec($q_select_pages);
    return $this->db->fetchAll();
    }

  public function fetchByOrder($order, $gl, $dir, $args)
    {
    $q_select_page = "SELECT * FROM `PREFIX_pages` WHERE `parent` = ? "
      . "AND `id` != ? AND `order` " . $gl . " ? ORDER BY `order` "
      . $dir . " LIMIT 1";

    $args[] = $order;
    $this->db->exec($q_select_page, 'iii', $args);
    return $this->db->fetch('Kpage', array($this));
    }

  public function insert($query, $types, $values)
    {
    $q_insert_page = "INSERT INTO `PREFIX_pages` " . $query;
    $this->db->exec($q_insert_page, $types, $values);
    return $this->db->insert_id;
    }

  public function update($query, $types, $values, $id)
    {
    $q_update_page = "UPDATE `PREFIX_pages` SET " . $query . " WHERE `id` = ?";
    $values[] = $id;
    return $this->db->exec($q_update_page, $types . 'i', $values);
    }

  public function delete($id)
    {
    $q_delete_page = "DELETE FROM `PREFIX_pages` WHERE `id` = ?";
    return $this->db->exec($q_delete_page, 'i', array($id));
    }
  }
?>