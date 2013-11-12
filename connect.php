<?

/**
Configure the following in db.properties
$dbhost = 'localhost';
$dbuser = 'iplog_user';
$dbpass = 'iplog_password';
$dbname = 'iplog_db';
**/

include("db.properties");

($dbh = mysql_pconnect($dbhost, $dbuser, $dbpass));

mysql_set_charset('utf8', $dbh) || die("Cannot set charset");

mysql_select_db($dbname) || die("Cannot select dbname");

?>
