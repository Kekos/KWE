<?php
/**
 * KWE Model: LanguageModel
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-08-09
 * @version 1.0
 */

class LanguageModel
  {
  private $db = null;

  public function __construct($db)
    {
    $this->db = $db;
    }

  public function fetch($id)
    {
    $q_select_language = "SELECT * FROM `PREFIX_languages` WHERE `id` = ?";

    $this->db->exec($q_select_language, 'i', array($id));
    return $this->db->fetch('Klanguage', array($this));
    }

  public function fetchAll()
    {
    $q_select_languages = "SELECT * FROM `PREFIX_languages` ORDER BY `id`";

    $this->db->exec($q_select_languages);
    return $this->db->fetchAll();
    }

  public function insert($query, $types, $values)
    {
    $q_insert_language = "INSERT INTO `PREFIX_languages` " . $query;
    $this->db->exec($q_insert_language, $types, $values);
    return $this->db->insert_id;
    }

  public function delete($id)
    {
    $q_delete_language = "DELETE FROM `PREFIX_languages` WHERE `id` = ?";
    return $this->db->exec($q_delete_language, 'i', array($id));
    }
  }
?>