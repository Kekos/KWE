<?php
define('PERMISSION_ADD', 1);
define('PERMISSION_EDIT', 2);
define('PERMISSION_DELETE', 4);
define('KWE_VERSION', '3.0');
define('KWE_BUILD', '110617');

require('admin_config.php');
require(BASE . 'include/init.php');

$route = (isset($_GET['r']) ? $_GET['r'] : '');
$db = db_mysqli::getInstance();

$request = new request(new session(), new cookie());
$page_model = new page_model_admin();

if ($user = $request->session->get('admin'))
  {
  $model_user = new user_model($db);
  new access($model_user, $user);
  }

$router = new router($route, $request, $page_model);
$page = $router->getPage();

$page->response->data['stylesheets'] = array();
$page->response->data['scripts'] = array();

$page->runControllers();

echo $page->getResponseContent();
?>