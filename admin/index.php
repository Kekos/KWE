<?php
define('PERMISSION_ADD', 1);
define('PERMISSION_EDIT', 2);
define('PERMISSION_DELETE', 4);
define('KWE_VERSION', '3.0');
define('KWE_BUILD', '120810');

require('admin_config.php');
require(BASE . 'include/init.php');

$route = (isset($_GET['r']) ? $_GET['r'] : '');
$db = DbMysqli::getInstance();

$request = new Request(new Session(), new Cookie());
$page_model = new PageModelAdmin();

Language::configure($request, LANGUAGE_SESSION, LANGUAGE_DEFAULT);

if ($user = $request->session->get('admin'))
  {
  $model_user = new UserModel($db);
  new Access($model_user, $user);
  Language::fromSession();
  }
else
  {
  Language::acceptHeader();
  }

Language::load('admin');

$router = new Router($route, $request, $page_model);
$page = $router->getPage();

$page->response->data['stylesheets'] = array();
$page->response->data['scripts'] = array();

$page->runControllers();

echo $page->getResponseContent();
?>