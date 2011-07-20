<?php
require('admin_config.php');
require(BASE . 'include/init.php');

$db = db_mysqli::getInstance();

$request = new request(new session(), new cookie());
$model_page = new page_model($db);

if ($user = $request->session->get('admin'))
  {
  $model_user = new user_model($db);
  new access($model_user, $user);
  }

if (!access::$is_logged_in)
  die('Du har inte tillgång till denna sida.');

define('IMAGE_PATH', '../images/upload/');
$allow_types = array('.png', '.gif', '.jpg', '.jpeg');
$fullpath = FULLPATH . '/images/upload';

$folder_url = (isset($_GET['folder']) ? $_GET['folder'] : '');
?>
<!DOCTYPE html
PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html lang="sv" xmlns="http://www.w3.org/1999/xhtml">

<head>
  <title>Bläddra bild - Leetitor</title>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <link type="text/css" href="<?php echo FULLPATH; ?>/../js/leetitor/leetitor.css" rel="stylesheet" media="screen" />
  <script type="text/javascript">
  function createNewFolder(in_folder)
    {
    var folder = prompt('Skriv in namnet på den nya mappen:', '');
    if (folder && folder != '')
      self.location.href = 'browse_image.php?new_folder=' + folder + '&in_folder=' + in_folder;
    }
  function selectImage(image_url)
    {
    window.opener.LeetitorImage('<?php echo $fullpath; ?>' + image_url);
    window.close();
    }
  </script>
</head>

<body id="leetitor_dialog">

<h1>Bläddra bild</h1>

<div id="footer">
  <input type="button" id="btn_browse" value="Bläddra" onclick="self.location.href = 'browse_image.php';" />
  <input type="button" id="btn_upload" value="Ladda upp" onclick="self.location.href = 'browse_image.php?upload=1&amp;folder=<?php echo $folder_url; ?>';" />
</div>

<?php
if (isset($_GET['upload']))
  {
  if ($request->post('upload_submit'))
    {
    $filename = $_FILES['upload']['name'];
    $new_filename = IMAGE_PATH . $request->get('folder') . '/' . urlSafe($filename, '\.');
    if (!in_array(substr($filename, -4), $allow_types) && !in_array(substr($filename, -5), $allow_types))
      echo '<p>Bilden hade fel format.</p>';
    else if ($_FILES['upload']['size'] <= 0)
      echo '<p>Bilden var för liten.</p>';
    else if (file_exists($new_filename))
      echo '<p>Det fanns redan en bild med samma namn!</p>';
    else
      {
      move_uploaded_file($_FILES['upload']['tmp_name'], $new_filename);
      echo '<p>Bilden <em>' . $filename . '</em> har laddats upp.</p>';
      }
    }
?>
<form action="browse_image.php?upload=1&amp;folder=<?php echo $request->get('folder'); ?>" method="post" enctype="multipart/form-data">
  <h3>Fil att ladda upp</h3>
  <p>(Endast *.png, *.gif, *.jpg, *.jpeg) <input type="file" name="upload" /></p>
  <p><input type="submit" name="upload_submit" value="OK, ladda upp" /></p>
</form>
<?php
  }
else if (isset($_GET['delete']))
  {
  $folder = $request->get('folder');
  $fileitem = $request->get('fileitem');
  $type = (is_dir(IMAGE_PATH . $folder . '/' . $fileitem)) ? 'mappen' : 'bilden';
  echo '<p>Är du säker på att du vill ta bort ' . $type . ' <em>' . $fileitem . '</em>? <a href="browse_image.php?delete_ok=' . $folder . '/' . $fileitem . '">Ja, radera!</a></p>';
  }
else if (isset($_GET['delete_ok']))
  {
  $file = $request->get('delete_ok');
  if (file_exists(IMAGE_PATH . $file))
    {
    $rm = IMAGE_PATH . $file;
    $type = 'Bilden';
    if (is_dir($rm))
      {
      $type = 'Mappen';
      rmdir($rm);
      }
    else
      unlink($rm);
    echo '<p>' . $type . ' <em>' . $file . '</em> har raderats.</p>';
    }
  else
    echo '<p><strong>Fel!</strong> Filen / mappen hittades inte...</p>';
  }
else if (isset($_GET['new_folder']))
  {
  $new_folder = $request->get('new_folder');
  mkdir(IMAGE_PATH . $request->get('in_folder') . '/' . urlSafe($new_folder));
  echo '<p>Mappen <em>' . $new_folder . '</em> skapades.</p>';
  }
else
  {
?>
<form action="browse_image.php" method="get">
  <input type="hidden" name="folder" value="<?php echo $folder_url; ?>" />
  <p>
    <input type="submit" name="delete" value="Ta bort" />
    <input type="button" id="new_folder" value="Ny mapp..." onclick="createNewFolder('<?php echo $folder_url; ?>');" />
  </p>

  <ul>
<?php
  $folder_arr = array();
  $files_arr = array();
  foreach (scandir(IMAGE_PATH . $folder_url) as $file)
    {
    if ($file == '.' || ($file == '..' && empty($folder_url)))
      continue;

    $full_file = $folder_url . '/' . $file;
    $allow_btns = ($file == '..') ? 0 : 1;

    $file_ext4 = substr($file, -5);
    $file_ext3 = substr($file, -4);
    if (is_dir(IMAGE_PATH . $full_file))
      $folder_arr[] = array($file, $allow_btns);
    else if (in_array($file_ext4, $allow_types) || in_array($file_ext3, $allow_types))
      $files_arr[] = $file;
    }

  foreach ($folder_arr as $folder)
    {
    if ($folder[0] == '..')
      {
      $url = substr($folder_url, 0, strrpos($folder_url, '/'));
      $folder[0] = 'Uppåt';
      }
    else
      $url = $folder_url . '/' . $folder[0];
    echo ($folder[1]) ? '    <input type="radio" name="fileitem" value="' . $folder[0] . '" />' : '    ';
    echo ' <a href="browse_image.php?folder=' . $url . '">' . $folder[0] . "</a><br />\n";
    }

  foreach ($files_arr as $file)
    {
    echo '    <li><input type="radio" name="fileitem" value="' . $file . '" /> <a href="javascript: selectImage(\'' . $folder_url . '/' . $file . '\');">' . $file . "</a></li>\n";
    }
  echo "  </ul>\n</form>\n";
  }
?>

</body>

</html>