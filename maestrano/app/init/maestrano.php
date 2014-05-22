<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
if (!defined('MAESTRANO_ROOT')) {
  define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));
}

//-----------------------------------------------
// Require Maestrano library
//-----------------------------------------------
require_once MAESTRANO_ROOT . '/lib/maestrano-php/lib/Maestrano.php';

//-----------------------------------------------
// Require Application Related Files
//-----------------------------------------------
define('MNO_APP_DIR', MAESTRANO_ROOT . '/app/');
require MNO_APP_DIR . '/sso/User.php';
require MNO_APP_DIR . '/sso/Group.php';

// Require Config files
require MAESTRANO_ROOT . '/app/config/maestrano.php';
  
