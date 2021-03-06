<?php
//-----------------------------------------------
// Define root folder and load base
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}
require_once MAESTRANO_ROOT . '/app/init/base.php';

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
if (!defined('CL_ROOT')) {
	define('CL_ROOT', realpath(MAESTRANO_ROOT . '/../'));
}
require_once CL_ROOT . '/include/initfunctions.php';
require_once CL_ROOT . '/include/class.mylog.php';
require_once CL_ROOT . '/include/class.user.php';
require_once CL_ROOT . '/include/class.roles.php';
require_once CL_ROOT . '/config/standard/config.php';

//-----------------------------------------------
// Perform your custom preparation code
//-----------------------------------------------
// Set options to pass to the MnoSsoUser
$opts = array();
if (!empty($db_name) and !empty($db_user)) {
    // $tdb = new datenbank();
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    
    $opts['db_connection'] = $conn;
}


