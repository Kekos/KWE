<?php
#$start = microtime();
require('config.php');
require(BASE . 'include/init.php');

$route = (isset($_GET['r']) ? $_GET['r'] : '');
$db = DbMysqli::getInstance();

$request = new Request(new Session(), new Cookie());
$page_model = new PageModel($db);

$router = new Router($route, $request, $page_model);
$page = $router->getPage();
$page->runControllers();

$page->response->data['navigation'] = $page_model->fetchPageList();
if ($page->page->parent == 0)
  $page->response->data['subnavigation'] = $page_model->fetchSubPageList($page->page->id);
else
  $page->response->data['subnavigation'] = $page_model->fetchSubPageList($page->page->parent);

echo $page->getResponseContent();

#echo "\n<!--\n";
#echo "page generated in ".round((microtime() - $start), 4)." seconds";
#echo "\n-->";
?>