<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
echo MAESTRANO_ROOT; 

//-----------------------------------------------
// Load Libraries & Settings
//-----------------------------------------------
require MAESTRANO_ROOT . '/app/init/_lib_loader.php';
require MAESTRANO_ROOT . '/app/settings.php'; //set $mno_settings variable

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
// Start database connection
if (!empty($db_name) and !empty($db_user)) {
    // $tdb = new datenbank();
    $conn = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
}

