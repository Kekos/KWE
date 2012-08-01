<?php
$lang['SETUP'] = 'Setup';
$lang['NEXT_STEP'] = 'Nästa steg';

$lang['HEADER_DATABASE'] = 'Databasinställningar';
$lang['HELP_DATABASE'] = 'KWE behöver en MySQL-databas att spara sidor, moduler och användare i. 
        Ange inloggningsuppgifter till databasservern här, vad databasen heter 
        samt ett unikt prefix för denna installation. Prefixet möjliggör flera 
        samtidiga installationer av KWE i samma databas.';
$lang['MYSQL_SERVER'] = 'MySQL-server';
$lang['MYSQL_USER'] = 'MySQL-användare';
$lang['MYSQL_PW'] = 'MySQL-lösenord';
$lang['MYSQL_DB'] = 'MySQL-databas';
$lang['PREFIX'] = 'Prefix för tabeller i databasen';

$lang['ADMIN_ACCOUNT'] = 'Administratörskonto';
$lang['HELP_ACCOUNT'] = 'Du måste skapa ett första administratörskonto, som får fullständiga 
        rättigheter i denna KWE-installation. Kom därför ihåg lösenordet och 
        förvara det säkert! Du kan skapa fler konton när du sedan loggat in.';
$lang['FIRST_LASTNAME'] = 'Ditt för- och efternamn';
$lang['USERNAME'] = 'Ditt användarnamn';
$lang['PASSWORD'] = 'Ditt lösenord';
$lang['REPEAT_PASSWORD'] = 'Repetera lösenordet';

$lang['HEADER_PATHS'] = 'Sökvägar och e-post';
$lang['HELP_PATHS'] = 'Detta är sista steget i installationen! Nu behövs bara ett par sökvägar.';
$lang['BASE'] = 'Sökväg till KWE-installation';
$lang['HELP_BASE'] = 'Måste sluta med snedstreck om den INTE är tom. Exempel: "kwe/" om KWE är installerat i mappen "kwe" men index.php ligger i roten.';
$lang['FULLPATH'] = 'URL till din index-fil relativt till webbserverns rot';
$lang['HELP_FULLPATH'] = 'Får inte sluta med snedstreck. Exempel: "/mysite" om man ska surfa till "http://mydomain.tld/mysite/"';
$lang['FULLURL'] = 'Fullständig URL till din index-fil';
$lang['HELP_FULLURL'] = 'Får inte sluta med snedstreck. Exempel: "http://mydomain.tld/mysite"';
$lang['USE_REWRITE'] = 'Använd URL-omskrivning';
$lang['NOT_USE_REWRITE'] = 'Använd INTE URL-omskrivning';
$lang['HELP_REWRITE'] = 'För att URL-omskrivning ska fungera måste det vara aktiverat i webbservern. I Apache heter modulen mod_rewrite.';
$lang['EMAIL'] = 'E-postadress till webbplatsansvarig';
$lang['HELP_EMAIL'] = 'Denna e-postadress visas för dina besökare om fel inträffar i mjukvaran. För att förhindra skräppost kan du ersätta @ med [at].';

$lang['HEADER_DONE'] = 'Grattis! Nu är installationen klar';
$lang['HELP_DONE'] = 'Alla inställningarna är gjorda och du kan börja använda KWE för att 
      publicera innehåll på din webbplats.';
$lang['LOGIN_ADMIN_LINK'] = 'Börja med att <a href="%s">logga in i administrationen</a>.';
$lang['HEADER_IMPORTANT'] = 'Viktigt!';
$lang['HELP_IMPORTANT'] = 'Det är <strong>mycket viktigt</strong> att du raderar 
      installationsfilen (setup.php) när du verifierat att installationen är 
      korrekt. Annars är det möjligt för andra personer att förstöra din 
      installation helt.';

$lang['COPYRIGHT'] = 'Copyright &copy; %s, %s';

$lang['ERROR_SQL_NOT_FOUND'] = 'Korrupt installation! SQL-filen hittades inte.';
$lang['ERROR_MISSING_DB_INFO'] = 'Alla fält måste fyllas i, förutom lösenord.';
$lang['ERROR_QUERY_EXEC'] = 'Kunde inte köra fråga: ';
$lang['ERROR_USERNAME'] = 'Skriv in ett längre användarnamn eller välj ett annat användarnamn.';
$lang['ERROR_PASSWORD'] = 'Ett lösenord måste innehålla minst 6 tecken.';
$lang['ERROR_NAME'] = 'Skriv in ett längre namn.';
$lang['ERROR_PASSWORD_MISSMATCH'] = 'De två lösenorden du skrev in stämde inte överens.';
?>