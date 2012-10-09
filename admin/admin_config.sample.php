<?php
define('DEBUG', ($_SERVER['REMOTE_ADDR'] == '127.0.0.1'));

define('MySQL_SERVER', 'localhost');
define('MySQL_USER', 'user');
define('MySQL_PASSWORD', 'password');
define('MySQL_DB', 'db_name');
define('DB_PREFIX', '');

define('MINIFIED', 0);  // True if the RESPONSE_LAYOUT should use the minified versions of CSS and JS
define('RESPONSE_LAYOUT', 'view/admin/layout.phtml');  // The main template file in which other templates will be included in
define('ERROR_LAYOUT', 'view/admin/error_layout.phtml');  // The error template file in which other error templates will be included in
define('LANGUAGE_SESSION', 1);  // True if the language selection should be auto-saved in session
define('LANGUAGE_DEFAULT', 'en');  // The language code of default language to use

define('BASE', '../');  // Base server path relative to admin interface, should end with / if not empty
define('BASE_SITE', '');  // Base server path, should end with / if not empty
define('FULLPATH', '/admin');  // Full server path to admin root, should not end with /
define('FULLURL', 'http://domain/admin');  // Full URL to admin root, should not end with /
define('FULLPATH_SITE', '');  // Full server path to site root, should not end with /
define('FULLURL_SITE', 'http://domain');  // Full URL to site root, should not end with /
define('ERROR_LOG_PATH', '');  // If empty KWF logs errors to your root (BASE). Don't add the value of BASE to this!

define('MOD_REWRITE', 1);
define('SITE_UP', 0);
define('META_KEYWORDS', '');
define('META_DESCRIPTION', '');
define('SESSION_PREFIX', 'kwe');
define('ERROR_MAIL', 'your[at]mail[dot]net');
?>