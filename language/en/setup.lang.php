<?php
$lang['SETUP'] = 'Setup';
$lang['NEXT_STEP'] = 'Next step';

$lang['HEADER_DATABASE'] = 'Database settings';
$lang['HELP_DATABASE'] = 'KWE needs a MySQL database for saving pages, modules 
        and users in. Enter user credentials for the database server here, the 
        name of your database and an unique prefix for this installation. The 
        prefix makes it possible to have multiple installations of KWE in the same database.';
$lang['MYSQL_SERVER'] = 'MySQL server';
$lang['MYSQL_USER'] = 'MySQL user';
$lang['MYSQL_PW'] = 'MySQL password';
$lang['MYSQL_DB'] = 'MySQL database';
$lang['PREFIX'] = 'Prefix for tables in the database';

$lang['ADMIN_ACCOUNT'] = 'Administrator\'s account';
$lang['HELP_ACCOUNT'] = 'You have to create a first administrator account, 
        that will gain full permissions in this KWE installation. Therefore, 
        you must remember this password and keep it safe! You can create more 
        accounts later when you have logged in.';
$lang['FIRST_LASTNAME'] = 'Your first- and lastname';
$lang['USERNAME'] = 'Your username';
$lang['PASSWORD'] = 'Your password';
$lang['REPEAT_PASSWORD'] = 'Repeat the password';

$lang['HEADER_PATHS'] = 'Paths and e-mail';
$lang['HELP_PATHS'] = 'This is the last step in the installation! Just some file paths is needed.';
$lang['BASE'] = 'Path to this KWE installation';
$lang['HELP_BASE'] = 'Must end with a slash if it\'s UNEMPTY. Example: "kwe/" if KWE is installed in the folder "kwe" but index.php is stored in the root.';
$lang['FULLPATH'] = 'URL to your index file relative to the web server\'s root';
$lang['HELP_FULLPATH'] = 'Must not end with slash. Example: "/mysite" if visitors should browse to "http://mydomain.tld/mysite/"';
$lang['FULLURL'] = 'Complete URL to your index file';
$lang['HELP_FULLURL'] = 'Must not end with slash. Example: "http://mydomain.tld/mysite"';
$lang['USE_REWRITE'] = 'Use URL rewrite';
$lang['NOT_USE_REWRITE'] = 'Do NOT use URL rewrite';
$lang['HELP_REWRITE'] = 'For URL rewrite to work it has to be activated in the web server. In Apache the module\'s name is mod_rewrite.';
$lang['EMAIL'] = 'E-mail address to webmaster';
$lang['HELP_EMAIL'] = 'This e-mail address is shown to your visitors if any errors occures in the software. To prevent spam, replace @ with [at].';

$lang['HEADER_DONE'] = 'Congratulations! The setup is now complete';
$lang['HELP_DONE'] = 'All settings are made and you can now start using KWE to 
      publish content on your web site.';
$lang['LOGIN_ADMIN_LINK'] = 'Start by <a href="%s">logging in to the administraton panel</a>.';
$lang['HEADER_IMPORTANT'] = 'Important!';
$lang['HELP_IMPORTANT'] = 'It is <strong>very important</strong> that you delete 
      the setup file (setup.php) when you have verified that the installation 
      is correct. Or else it is possible for other users to destroy your 
      installation completely.';

$lang['COPYRIGHT'] = 'Copyright &copy; %s, %s';

$lang['ERROR_SQL_NOT_FOUND'] = 'Corrupt installation! The SQL file could not be found.';
$lang['ERROR_MISSING_DB_INFO'] = 'Every field must be filled in, except from password.';
$lang['ERROR_QUERY_EXEC'] = 'Could not execute query: ';
$lang['ERROR_USERNAME'] = 'Please enter a longer username, or select an other username.';
$lang['ERROR_PASSWORD'] = 'A password must contain at least 6 characters.';
$lang['ERROR_NAME'] = 'Please enter a longer name.';
$lang['ERROR_PASSWORD_MISSMATCH'] = 'The two passwords you entered didn\'t match.';
?>