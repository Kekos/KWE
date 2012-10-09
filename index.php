<?php
#$start = microtime();
require('config.php');
require(BASE . 'include/init.php');

if (SITE_UP)
  {
  $route = (isset($_GET['r']) ? $_GET['r'] : '');
  $db = DbMysqli::getInstance();

  $request = new Request(new Session(), new Cookie());
  $page_model = new PageModel($db);

  $index_events = new EventListener('BootStrap');
  $index_events->dispatchEvent('beforerouter', array('request' => $request));

  $router = new Router($route, $request, $page_model);
  $page = $router->getPage();
  $page->runControllers();

  $index_events->dispatchEvent('beforenavigationload', array('response' => $page->response));

  $page->response->data['navigation'] = $page_model->fetchPageList();
  if ($page->page->parent == 0)
    $page->response->data['subnavigation'] = $page_model->fetchSubPageList($page->page->id);
  else
    $page->response->data['subnavigation'] = $page_model->fetchSubPageList($page->page->parent);

  $index_events->dispatchEvent('beforecontent', array('response' => $page->response));

  echo $page->getResponseContent();
  }
else
  {
  require(BASE . 'view/shutdown.phtml');
  }

#echo "\n<!--\n";
#echo "page generated in ".round((microtime() - $start), 4)." seconds";
#echo "\n-->";
?>