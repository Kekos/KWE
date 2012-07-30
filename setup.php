<?php
error_reporting(0); // Yes, this is ugly. But it removes notice about BASE is already defined in CONFIG_FILE

define('BASE', '');
define('SQL_FILE', 'kwe-structure.sql');
define('CONFIG_FILE', 'config.php');
require('include/functions.php');

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
  $fatal = 'Korrupt installation! SQL-filen hittades inte.';
  }
else if (isset($_POST['step1']))
  {
  $settings = $_POST['setting'];

  if (empty($settings['MySQL_SERVER']) || empty($settings['MySQL_USER']) || empty($settings['MySQL_DB']) || empty($settings['DB_PREFIX']))
    {
    $errors[] = 'Alla fält måste fyllas i, förutom lösenord.';
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
            $errors[] = 'Kunde inte köra fråga: ' . $db->error;
            break;
            }
          }
        catch (Exception $ex)
          {
          $errors[] = 'Kunde inte köra fråga: ' . $ex->getMessage();
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
    $errors[] = 'Skriv in ett längre användarnamn eller välj ett annat användarnamn.';
  if (!$user->setPassword($password))
    $errors[] = 'Ett lösenord måste innehålla minst 6 tecken.';
  if (!$user->setName($name))
    $errors[] = 'Skriv in ett längre namn.';
  if (md5($password) !== md5($_POST['repeat_password']))
    $errors[] = 'De två lösenorden du skrev in stämde inte överens.';

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
<html lang="sv">

<head>
  <title>KWE 3.0 Setup</title>
  <meta charset="utf-8" />
  <link href="admin/css/kwe_admin.css" rel="stylesheet" />
  <link href="admin/css/kwe_setup.css" rel="stylesheet" />
  <!--[if lte IE 8]><script src="js/html5.min.js" type="text/javascript"></script><![endif]-->
</head>

<body>

<header id="header">
  <h1>KWE 3.0 Setup</h1>
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
      <h1>1: Databasinställningar</h1>
      <p>KWE behöver en MySQL-databas att spara sidor, moduler och användare i. 
        Ange inloggningsuppgifter till databasservern här, vad databasen heter 
        samt ett unikt prefix för denna installation. Prefixet möjliggör flera 
        samtidiga installationer av KWE i samma databas.</p>
      <ol>
        <li><label for="mysql_server">MySQL-server</label> <input type="text" name="setting[MySQL_SERVER]" id="mysql_server"<?php Request::formStatePost('setting[MySQL_SERVER]', 'text'); ?> /></li>
        <li><label for="mysql_user">MySQL-användare</label> <input type="text" name="setting[MySQL_USER]" id="mysql_user"<?php Request::formStatePost('setting[MySQL_USER]', 'text'); ?> /></li>
        <li><label for="mysql_password">MySQL-lösenord</label> <input type="password" name="setting[MySQL_PASSWORD]" id="mysql_password"<?php Request::formStatePost('setting[MySQL_PASSWORD]', 'text'); ?> /></li>
        <li><label for="mysql_db">MySQL-databas</label> <input type="text" name="setting[MySQL_DB]" id="mysql_db"<?php Request::formStatePost('setting[MySQL_DB]', 'text'); ?> /></li>
        <li><label for="db_prefix">Prefix för tabeller i databasen</label> <input type="text" name="setting[DB_PREFIX]" id="db_prefix"<?php Request::formStatePost('setting[DB_PREFIX]', 'text'); ?> /></li>
        <li><button type="submit" name="step1" value="yes">Nästa steg</button></li>
      </ol>
    </fieldset>
<?php
/* STEP 2: Define admin user account */
elseif ($step == 2): ?>
    <fieldset>
      <h1>2: Administratörskonto</h1>
      <p>Du måste skapa ett första administratörskonto, som får fullständiga 
        rättigheter i denna KWE-installation. Kom därför ihåg lösenordet och 
        förvara det säkert! Du kan skapa fler konton när du sedan loggat in.</p>
      <ol>
        <li><label for="name">Ditt för- och efternamn</label> <input type="text" name="name" id="name"<?php Request::formStatePost('name', 'text'); ?> /></li>
        <li><label for="username">Ditt användarnamn</label> <input type="text" name="username" id="username"<?php Request::formStatePost('username', 'text'); ?> /></li>
        <li><label for="password">Ditt lösenord</label> <input type="password" name="password" id="password" /></li>
        <li><label for="repeat_password">Repetera lösenordet</label> <input type="password" name="repeat_password" id="repeat_password" /></li>
        <li><button type="submit" name="step2" value="yes">Nästa steg</button></li>
      </ol>
    </fieldset>
<?php
/* STEP 3: Define all other config settings */
elseif ($step == 3): ?>
    <fieldset>
      <h1>3: Sökvägar och e-post</h1>
      <p>Detta är sista steget i installationen! Nu behövs bara ett par sökvägar.</p>
      <ol>
        <li><label for="base">Sökväg till KWE-installation</label> <input type="text" name="setting[BASE]" id="base"<?php Request::formStatePost('setting[BASE]', 'text'); ?> />
            <span class="description">Måste sluta med snedstreck om den INTE är tom. Exempel: "kwe/" om KWE är installerat i mappen "kwe" men index.php ligger i roten.</span></li>
          <li><label for="fullpath">URL till din index-fil relativt till webbserverns rot</label> <input type="text" name="setting[FULLPATH]" id="fullpath"<?php Request::formStatePost('setting[FULLPATH]', 'text'); ?> />
            <span class="description">Får inte sluta med snedstreck. Exempel: "/mysite" om man ska surfa till "http://mydomain.tld/mysite/"</span></li>
          <li><label for="fullurl">Fullständig URL till din index-fil</label> <input type="text" name="setting[FULLURL]" id="fullurl"<?php Request::formStatePost('setting[FULLURL]', 'text'); ?> />
            <span class="description">Får inte sluta med snedstreck. Exempel: "http://mydomain.tld/mysite"</span></li>
          <li><label for="mod_rewrite_on">Använd URL-omskrivning</label> <input type="radio" name="setting[MOD_REWRITE]" value="1" id="mod_rewrite_on"<?php Request::formStatePost('setting[MOD_REWRITE]', 'radio'); ?> />
            <label for="mod_rewrite_off">Använd INTE URL-omskrivning</label> <input type="radio" name="setting[MOD_REWRITE]" value="0" id="mod_rewrite_off"<?php Request::formStatePost('setting[MOD_REWRITE]', 'radio'); ?> />
            <span class="description">För att URL-omskrivning ska fungera måste det vara aktiverat i webbservern. I Apache heter modulen mod_rewrite.</span></li>
          <li><label for="error_mail">E-postadress till webbplatsansvarig</label> <input type="text" name="setting[ERROR_MAIL]" id="error_mail"<?php Request::formStatePost('setting[ERROR_MAIL]', 'text'); ?> />
            <span class="description">Denna e-postadress visas för dina besökare om fel inträffar i mjukvaran. För att förhindra skräppost kan du ersätta @ med [at].</span></li>
        <li><button type="submit" name="step3" value="yes">Nästa steg</button></li>
      </ol>
    </fieldset>
<?php
/* STEP 4: Display greetings for user! */
elseif ($step == 4): ?>
    <h1>Grattis! Nu är installationen klar</h1>
    <p>Alla inställningarna är gjorda och du kan börja använda KWE för att 
      publicera innehåll på din webbplats.</p>
    <p>Börja med att <a href="admin/">logga in i administrationen</a>.</p>

    <h2>Viktigt!</h2>
    <p>Det är <strong>mycket viktigt</strong> att du raderar 
      installationsfilen (setup.php) när du verifierat att installationen är 
      korrekt. Annars är det möjligt för andra personer att förstöra din 
      installation helt.</p>
<?php endif; ?>
  </form>
<?php endif; ?>
</div>

<footer id="footer">
  <p>Copyright &copy; <a href="http://kekos.se/">Christoffer Lindahl</a>, 2009-2012</p>
</footer>

</body>

</html>