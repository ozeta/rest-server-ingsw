<?php
//AZURE
/*
$dbHost = "eu-cdbr-azure-north-e.cloudapp.net";
$dbUser = "b6fbc352e586be";
$dbPassword = "6e642ab7";
$tableName = "softengunina.user";
*/


/**localhost
*/
//ini_set('display_errors', '0');     # don't show any errors...
//error_reporting(E_ALL | E_STRICT);  # ...but do log them

/*
define("DBHOST", "localhost");
define("USERSCHEMA", "ING10");
define("DBTABLE", "user");
define("DBUSER", "root");
define("DBPASSWORD", "");
*/

//altervista
define("DBHOST", "localhost");
define("USERSCHEMA", "my_softengunina10");
define("DBTABLE", "user");
define("DBUSER", "softengunina10");
define("DBPASSWORD", "");
define("VERBOSE", true);

define("QCFCODE", -1);
define("QUSERCODE", -2);
define("QPIVACODE", -3);
define("QPHONECODE", -4);
define("QEMAILCODE", -5);

define("CFCODE", 420);
define("USERCODE", 421);
define("PIVACODE", 422);
define("PHONECODE", 423);
define("EMAILCODE", 424);
/*
//aws ec2-54-186-152-131.us-west-2.compute.amazonaws.com
define("DBHOST", "localhost");
define("USERSCHEMA", "gci16");
define("DBUSER", "root");
define("DBPASSWORD", "");
define("VERBOSE", true);
define("NONVERBOSE", false);

*/
/*
function __autoload($class_name) {
    require_once $class_name . '.php';
}


public class Settings{
    public $DBHOST = "localhost";
}
*/
?>