<?php
define('BASE', '../');
require(BASE . 'include/init.php');

$db = new db();

$request = new request(new session());
$model_page = new page_model($db);

new user_model($db, $request->session->get('admin'));

if (!user_model::$is_logged_in)
  die('Du har inte tillgång till denna sida.');

$page = $request->get('page');
if ($page && !empty($page))
  {
  $page = $model_page->getPage($page);
  $pages = $model_page->fetchSubPageList($page['id'], 0, 1);
  }
else
  {
  $pages = $model_page->fetchPageList(0, 1);
  }
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="sv" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Bläddra länk - Leetitor</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link type="text/css" href="<?php echo FULLPATH; ?>/js/leetitor/leetitor.css" rel="stylesheet" media="screen" />
  <script type="text/javascript">
  function selectLink(page)
    {
    window.opener.elem('link_url').value = '<?php echo FULLPATH . (MOD_REWRITE ? '' : '?r='); ?>/' + page + '/';
    window.close();
    }
  </script>
</head>

<body id="leetitor_dialog">

<h1>Bläddra länk</h1>

<?php if ($page): ?>
<h3>Välj en undersida</h3>

<p><strong>Vald sida:</strong> <?php echo htmlspecialchars($page['title']); ?> (<a href="browse_link.php">Tillbaka</a>)</p>

<ul>
<?php foreach ($pages as $subpage): ?>
  <li><a href="javascript: selectLink('<?php echo htmlspecialchars($subpage['url']) ?>');"><?php echo htmlspecialchars($subpage['title']) ?></a></li>
<?php endforeach;
else: ?>
<h3>Välj en sida</h3>

<ul>
<?php foreach ($pages as $page): ?>
  <li><a href="browse_link.php?page=<?php echo $page['url']; ?>"><?php echo $page['title']; ?></a></li>
<?php endforeach;
endif; ?>
</ul>

</body>

</html>