<?php
error_reporting(0); // Yes, this is ugly. But it removes notice about BASE is already defined in CONFIG_FILE

define('BASE', '');
define('SQL_FILE', 'kwe-structure.sql');
define('CONFIG_FILE', 'config.php');
define('KWE_VERSION', '3.0');
require('include/functions.php');

$request = new Request(new Session());
Language::configure($request, false, 'en');
Language::acceptHeader();
Language::load('setup');

function saveSettings($settings)
  {
  $admin_config_filename = 'admin/admin_config.php';

  $config_content = file_get_contents(CONFIG_FILE);
  $admin_config_content = file_get_contents($admin_config_filename);

  foreach ($settings as $setting => &$sett_value)
    {
    if (is_numeric($sett_value))
      {
      $config_content = preg_replace("/define\('" . $setting . "', (.*?)\);/s", "define('" . $setting . "', " . $sett_value . ");", $config_content);
      $admin_config_content = preg_replace("/define\('" . $setting . "', (.*?)\);/s", "define('" . $setting . "', " . $sett_value . ");", $admin_config_content);
      }
    else
      {
      switch ($setting)
        {
        case 'BASE':
          $settings['BASE_SITE'] = $sett_value;
          if ($sett_value == '')
            $admin_value = '../';
          else
            $admin_value = substr($sett_value, 0, strrpos($sett_value, '/'));
          break;
        case 'FULLPATH':
          $settings['FULLPATH_SITE'] = $sett_value;
          $admin_value = $sett_value . '/admin';
          break;
        case 'FULLURL':
          $settings['FULLURL_SITE'] = $sett_value;
          $admin_value = $sett_value . '/admin';
          break;
        default:
          $admin_value = $sett_value;
        }

      $config_content = preg_replace("/define\('" . $setting . "', '(.*?)'\);/s", "define('" . $setting . "', '" . str_replace("'", "\\'", $sett_value) . "');", $config_content);
      $admin_config_content = preg_replace("/define\('" . $setting . "', '(.*?)'\);/s", "define('" . $setting . "', '" . str_replace("'", "\\'", $admin_value) . "');", $admin_config_content);
      }
    }

  file_put_contents(CONFIG_FILE,  $config_content);
  file_put_contents($admin_config_filename,  $admin_config_content);
  }

$fatal = '';
$errors = array();
$step = 1;

if (!file_exists(SQL_FILE))
  {
  $fatal = __('ERROR_SQL_NOT_FOUND');
  }
else if (isset($_POST['step1']))
  {
  $settings = $_POST['setting'];

  if (empty($settings['MySQL_SERVER']) || empty($settings['MySQL_USER']) || empty($settings['MySQL_DB']) || empty($settings['DB_PREFIX']))
    {
    $errors[] = __('ERROR_MISSING_DB_INFO');
    }
  else
    {
    saveSettings($settings);
    require(CONFIG_FILE);

    $db = DbMysqli::getInstance();
    $queries = explode(';', file_get_contents(SQL_FILE));

    foreach ($queries as $query)
      {
      $query = trim($query);
      if (!empty($query))
        {
        try
          {
          $query = str_replace('PREFIX', DB_PREFIX, $query);
          $query_result = @$db->query($query);
          if ($db->error)
            {
            $errors[] = __('ERROR_QUERY_EXEC') . $db->error;
            break;
            }
          }
        catch (Exception $ex)
          {
          $errors[] = __('ERROR_QUERY_EXEC') . $ex->getMessage();
          break;
          }
        }
      }

    if (!count($errors))
      {
      $step = 2;
      }
    }
  }
else if (isset($_POST['step2']))
  {
  require(CONFIG_FILE);
  $db = DbMysqli::getInstance();

  $username = $_POST['username'];
  $password = $_POST['password'];
  $name = $_POST['name'];

  $user = new User(new UserModel($db));
  $step = 2;

  if (!$user->setUsername($username))
    $errors[] = __('ERROR_USERNAME');
  if (!$user->setPassword($password))
    $errors[] = __('ERROR_PASSWORD');
  if (!$user->setName($name))
    $errors[] = __('ERROR_NAME');
  if (md5($password) !== md5($_POST['repeat_password']))
    $errors[] = __('ERROR_PASSWORD_MISSMATCH');

  if (!count($errors))
    {
    $user->setRank(1);
    $user->setOnline(0);
    $user->setOnlineTime();
    $user->save();

    $step = 3;
    }
  }
else if (isset($_POST['step3']))
  {
  $settings = $_POST['setting'];
  saveSettings($settings);
  $step = 4;
  }

?>
<!DOCTYPE html>
<html lang="sv" dir="<?php echo __('READ_DIRECTION'); ?>">

<head>
  <title>KWE <?php echo KWE_VERSION . ' ' . __('SETUP'); ?></title>
  <meta charset="utf-8" />
  <link href="admin/css/kwe_admin.css" rel="stylesheet" />
  <link href="admin/css/kwe_setup.css" rel="stylesheet" />
  <!--[if lte IE 8]><script src="js/html5.min.js" type="text/javascript"></script><![endif]-->
</head>

<body>

<header id="header">
  <h1>KWE <?php echo KWE_VERSION . ' ' . __('SETUP'); ?></h1>
</header>

<div id="content">
<?php if (!empty($fatal)): ?>
  <p><?php echo $fatal; ?></p>
<?php else:

if (count($errors)): ?>
  <ul id="errorlist">
<?php foreach ($errors as $error): ?>
    <li><?php echo $error; ?></li>
<?php endforeach; ?>
  </ul>
<?php endif; ?>

  <form action="setup.php" method="post">
<?php
/* STEP 1: Define database settings */
if ($step == 1) : ?>
    <fieldset>
      <h1>1: <?php echo __('HEADER_DATABASE'); ?></h1>
      <p><?php echo __('HELP_DATABASE'); ?></p>
      <ol>
        <li><label for="mysql_server"><?php echo __('MYSQL_SERVER'); ?></label> <input type="text" name="setting[MySQL_SERVER]" id="mysql_server"<?php Request::formStatePost('setting[MySQL_SERVER]', 'text'); ?> /></li>
        <li><label for="mysql_user"><?php echo __('MYSQL_USER'); ?></label> <input type="text" name="setting[MySQL_USER]" id="mysql_user"<?php Request::formStatePost('setting[MySQL_USER]', 'text'); ?> /></li>
        <li><label for="mysql_password"><?php echo __('MYSQL_PW'); ?></label> <input type="password" name="setting[MySQL_PASSWORD]" id="mysql_password"<?php Request::formStatePost('setting[MySQL_PASSWORD]', 'text'); ?> /></li>
        <li><label for="mysql_db"><?php echo __('MYSQL_DB'); ?></label> <input type="text" name="setting[MySQL_DB]" id="mysql_db"<?php Request::formStatePost('setting[MySQL_DB]', 'text'); ?> /></li>
        <li><label for="db_prefix"><?php echo __('PREFIX'); ?></label> <input type="text" name="setting[DB_PREFIX]" id="db_prefix"<?php Request::formStatePost('setting[DB_PREFIX]', 'text'); ?> /></li>
        <li><button type="submit" name="step1" value="yes"><?php echo __('NEXT_STEP'); ?></button></li>
      </ol>
    </fieldset>
<?php
/* STEP 2: Define admin user account */
elseif ($step == 2): ?>
    <fieldset>
      <h1>2: <?php echo __('ADMIN_ACCOUNT'); ?></h1>
      <p><?php echo __('HELP_ACCOUNT'); ?></p>
      <ol>
        <li><label for="name"><?php echo __('FIRST_LASTNAME'); ?></label> <input type="text" name="name" id="name"<?php Request::formStatePost('name', 'text'); ?> /></li>
        <li><label for="username"><?php echo __('USERNAME'); ?></label> <input type="text" name="username" id="username"<?php Request::formStatePost('username', 'text'); ?> /></li>
        <li><label for="password"><?php echo __('PASSWORD'); ?></label> <input type="password" name="password" id="password" /></li>
        <li><label for="repeat_password"><?php echo __('REPEAT_PASSWORD'); ?></label> <input type="password" name="repeat_password" id="repeat_password" /></li>
        <li><button type="submit" name="step2" value="yes"><?php echo __('NEXT_STEP'); ?></button></li>
      </ol>
    </fieldset>
<?php
/* STEP 3: Define all other config settings */
elseif ($step == 3): ?>
    <fieldset>
      <h1>3: <?php echo __('HEADER_PATHS'); ?></h1>
      <p><?php echo __('HELP_PATHS'); ?></p>
      <ol>
        <li><label for="base"><?php echo __('BASE'); ?></label> <input type="text" name="setting[BASE]" id="base"<?php Request::formStatePost('setting[BASE]', 'text'); ?> />
            <span class="description"><?php echo __('HELP_BASE'); ?></span></li>
          <li><label for="fullpath"><?php echo __('FULLPATH'); ?></label> <input type="text" name="setting[FULLPATH]" id="fullpath"<?php Request::formStatePost('setting[FULLPATH]', 'text'); ?> />
            <span class="description"><?php echo __('HELP_FULLPATH'); ?></span></li>
          <li><label for="fullurl"><?php echo __('FULLURL'); ?></label> <input type="text" name="setting[FULLURL]" id="fullurl"<?php Request::formStatePost('setting[FULLURL]', 'text'); ?> />
            <span class="description"><?php echo __('HELP_FULLURL'); ?></span></li>
          <li><label for="mod_rewrite_on"><?php echo __('USE_REWRITE'); ?></label> <input type="radio" name="setting[MOD_REWRITE]" value="1" id="mod_rewrite_on"<?php Request::formStatePost('setting[MOD_REWRITE]', 'radio'); ?> />
            <label for="mod_rewrite_off"><?php echo __('NOT_USE_REWRITE'); ?></label> <input type="radio" name="setting[MOD_REWRITE]" value="0" id="mod_rewrite_off"<?php Request::formStatePost('setting[MOD_REWRITE]', 'radio'); ?> />
            <span class="description"><?php echo __('HELP_REWRITE'); ?></span></li>
          <li><label for="error_mail"><?php echo __('EMAIL'); ?></label> <input type="text" name="setting[ERROR_MAIL]" id="error_mail"<?php Request::formStatePost('setting[ERROR_MAIL]', 'text'); ?> />
            <span class="description"><?php echo __('HELP_EMAIL'); ?></span></li>
        <li><button type="submit" name="step3" value="yes"><?php echo __('NEXT_STEP'); ?></button></li>
      </ol>
    </fieldset>
<?php
/* STEP 4: Display greetings for user! */
elseif ($step == 4): ?>
    <h1><?php echo __('HEADER_DONE'); ?></h1>
    <p><?php echo __('HELP_DONE'); ?></p>
    <p><?php echo __('LOGIN_ADMIN_LINK', 'admin/'); ?></p>

    <h2><?php echo __('HEADER_IMPORTANT'); ?></h2>
    <p><?php echo __('HELP_IMPORTANT'); ?></p>
<?php endif; ?>
  </form>
<?php endif; ?>
</div>

<footer id="footer">
  <p><?php echo __('COPYRIGHT', '<a href="http://kekos.se/">Christoffer Lindahl</a>', '2009-2012'); ?></p>
</footer>

</body>

</html>