<?php
define('DEBUG', ($_SERVER['REMOTE_ADDR'] == '127.0.0.1'));

define('MySQL_SERVER', 'localhost');
define('MySQL_USER', 'admincl');
define('MySQL_PASSWORD', 'smultron');
define('MySQL_DB', 'kwe');
define('DB_PREFIX', 'kwe');

define('RESPONSE_LAYOUT', 'view/admin/layout.phtml');  // The main template file in which other templates will be included in
define('ERROR_LAYOUT', 'view/admin/error_layout.phtml');  // The error template file in which other error templates will be included in

define('BASE', '../');  // Base server path relative to admin interface, should end with / if not empty
define('BASE_SITE', '');  // Base server path, should end with / if not empty
define('FULLPATH', '/admin');  // Full server path to admin root, should not end with /
define('FULLURL', 'http://kwe/admin');  // Full URL to admin root, should not end with /
define('FULLPATH_SITE', '');  // Full server path to site root, should not end with /
define('FULLURL_SITE', 'http://kwe');  // Full URL to site root, should not end with /

define('MOD_REWRITE', 1);
define('SESSION_PREFIX', 'kwe');
define('ERROR_MAIL', 'christoffer[snabel-a]kekos[punkt]se');
?>