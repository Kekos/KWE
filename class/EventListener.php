<?php
/**
 * KWF Class: EventListener, implements events or "hooks" in classes
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-10-09
 * @version 1.0
 */

class EventListener
  {
  private $listeners = array();
  private $classname = null;

  /*
   * Constructor: EventListener
   *
   * @param string $params Contains the params sent to page which started this controller
   * @return void
   */
	public function __construct($classname = null)
    {
    if (!$classname)
      {
      $classname = get_class();
      }

    $this->classname = $classname;
    $this->listeners = HookManager::get($classname);
    }

  /*
   * Dispatches an event type
   *
   * @param string $type The name of event to dispatch
   * @param object $event An event object
   * @return void
   */
	public function dispatchEvent($type, $event)
    {
    if (isset($this->listeners[$type]))
      {
      foreach ($this->listeners[$type] as $i => $row)
        {
        call_user_func($row, $event);
        }
      }
    }
  }
?>