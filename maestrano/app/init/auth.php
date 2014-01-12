<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!isset(MAESTRANO_ROOT)){
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

//-----------------------------------------------
// Load Libraries & Settings
//-----------------------------------------------
require MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require MAESTRANO_ROOT . '/app/init/_config_loader.php'; //set $mno_settings variable

//-----------------------------------------------
// Require your app specific files here
//-----------------------------------------------
define('COLLAB_DIR', realpath(MAESTRANO_ROOT . '/../'));
require COLLAB_DIR . '/include/initfunctions.php';
require COLLAB_DIR . '/include/class.mylog.php';
require COLLAB_DIR . '/include/class.user.php';
require COLLAB_DIR . '/include/class.roles.php';
require COLLAB_DIR . '/config/standard/config.php';

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


