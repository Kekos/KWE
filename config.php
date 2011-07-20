<?php
define('DEBUG', ($_SERVER['REMOTE_ADDR'] == '127.0.0.1'));

define('MySQL_SERVER', 'localhost');
define('MySQL_USER', 'admincl');
define('MySQL_PASSWORD', 'smultron');
define('MySQL_DB', 'kwe');
define('DB_PREFIX', 'kwe');

define('RESPONSE_LAYOUT', 'view/layout.phtml');  // The main template file in which other templates will be included in
define('ERROR_LAYOUT', 'view/error_layout.phtml');  // The error template file in which other error templates will be included in

define('BASE', '');  // Base server path, should end with / if not empty
define('FULLPATH', '');  // Full server path to root, should not end with /
define('FULLURL', 'http://kwe');  // Full URL to root, should not end with /

define('MOD_REWRITE', 1);
define('SESSION_PREFIX', 'kwe');
define('ERROR_MAIL', 'christoffer[snabel-a]kekos[punkt]se');
?>