<?php
/**
 * KWF Class: Access, handles log in and access check
 * 
 * @author Christoffer Lindahl <christoffer@kekos.se>
 * @date 2012-07-11
 * @version 1.1
 */

class Access
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
    if (self::$user->rank == 1)
      return true;

    $db = DbMysqli::getInstance();
    $model_controller_permission = new ControllerPermissionModel($db);
    return $model_controller_permission->fetch(self::$user->id, $controller);
    }
  }
?>