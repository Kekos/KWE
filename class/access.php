<?php
/**
 * KWF Class: access, handles log in and access check
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2011-06-10
 * @version 1.1
 */

class access
  {
  private $model;

  static $user = null;
  static $is_logged_in = false;
  static $is_administrator = null;

  /*
   * Constructor: access
   *
   * @param object $model The user model object to get user data from
   * @param int $user_id_logged_in ID of the logged in user
   * @return void
   */
  public function __construct($model, $user_id_logged_in)
    {
    $this->model = $model;

    if ($user_id_logged_in != false)
      {
      if (self::$user = $this->model->fetch($user_id_logged_in))
        {
        self::$is_logged_in = true;
        self::$is_administrator = (self::$user->rank < 3);

        self::$user->setOnlineTime();
        self::$user->save();
        }
      }
    }

  /*
   * Checks whether a user has permission to us a controller
   *
   * @param string $controller Name of controller to check permissions for
   * @return bool
   */
  static function hasControllerPermission($controller)
    {
    if (!self::$is_logged_in)
      return false;
    if (access::$user->rank == 1)
      return true;

    $db = db_mysqli::getInstance();
    $model_controller_permission = new controller_permission_model($db);
    return $model_controller_permission->fetch(access::$user->id, $controller);
    }
  }
?>