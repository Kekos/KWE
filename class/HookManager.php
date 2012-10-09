<?php
/**
 * KWF Class: HookManager, reads and writes to the hook file
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-09
 * @version 1.0
 */

define('HOOKS_CONF_FILE', BASE . 'class/hooks.conf');

class HookManager
  {
  static private $hooks = array();
  static private $loaded = false;

  /*
   * Loads all hooks from the hook config file
   *
   * @return void
   */
	static private function load()
    {
    if (!self::$loaded)
      {
      self::$hooks = unserialize(file_get_contents(HOOKS_CONF_FILE));
      self::$loaded = true;
      }
    }

  /*
   * Saves all hooks to the hook config file
   *
   * @return void
   */
	static public function save()
    {
    if (self::$loaded)
      {
      file_put_contents(HOOKS_CONF_FILE, serialize(self::$hooks));
      }
    }

  /*
   * Returns all hooks belonging to a specific class
   *
   * @param string $class The class name
   * @return array
   */
	static public function get($class)
    {
    self::load();

    if (isset(self::$hooks[$class]))
      {
      return self::$hooks[$class];
      }
    else
      {
      return array();
      }
    }

  /*
   * Adds event listener to class
   *
   * @param string $class The class name
   * @param string $type The name of event to add listener to
   * @param string $listener The name of listener function
   * @return void
   */
	static public function add($class, $type, $listener)
    {
    self::load();

    if (!isset(self::$hooks[$class]))
      {
      self::$hooks[$class] = array();
      }

    if (!isset(self::$hooks[$class][$type]))
      {
      self::$hooks[$class][$type] = array();
      }

    self::$hooks[$class][$type][] = $listener;
    }

  /*
   * Removes event listener from class
   *
   * @param string $class The class name
   * @param string $type The name of event to add listener to
   * @param string $listener The name of listener function
   * @return void
   */
	static public function remove($class, $type, $listener)
    {
    self::load();

    if (isset(self::$hooks[$class]) && isset(self::$hooks[$class][$type]))
      {
      foreach (self::$hooks[$class][$type] as $i => $row)
        {
        if ($row == $listener)
          {
          unset(self::$hooks[$class][$type][$i]);
          }
        }
      }
    }
  }
?>