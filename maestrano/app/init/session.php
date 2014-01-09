<?php
//-----------------------------------------------
// Define root folder
//-----------------------------------------------
define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

//------------------------------------------------
// Load Minimal set of libraries and config files
//------------------------------------------------
require MAESTRANO_ROOT . '/lib/mno-php/src/MnoSettings.php';
require MAESTRANO_ROOT . '/lib/mno-php/src/sso/MnoSsoSession.php';
require MAESTRANO_ROOT . '/app/settings.php'; //set $mno_settings variable