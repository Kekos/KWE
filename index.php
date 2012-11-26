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

  $navigation = null;
  $subnavigation = null;
  $index_events->dispatchEvent('beforenavigationload', array(
      'response' => $page->response, 
      'page' => $page, 
      'page_model' => $page_model, 
      'navigation' => &$navigation, 
      'subnavigation' => &$subnavigation));

  if ($navigation == null)
    {
    $navigation = $page_model->fetchPageList();
    if ($page->page->parent == 0)
      $subid = $page->page->id;
    else
      $subid = $page->page->parent;

    $subnavigation = $page_model->fetchSubPageList($subid);
    }

  $page->response->data['navigation'] = $navigation;
  $page->response->data['subnavigation'] = $subnavigation;

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