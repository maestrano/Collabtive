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
// Require Model customization files
//-----------------------------------------------
require_once MAESTRANO_ROOT . '/app/models/sso/User.php';
require_once MAESTRANO_ROOT . '/app/models/sso/Group.php';

//-----------------------------------------------
// Configure Maestrano
//-----------------------------------------------
Maestrano::configure(array(
  'environment'          => 'test',
  'api_key'              => 'gfcmbu8269wyi0hjazk4t7o1sndpvrqxl53e1',
  'app_host'             => 'http://localhost:8888',
  'sso_enabled'          => true,
  'sso_app_init_path'    => '/maestrano/auth/saml/index.php',
  'sso_app_consume_path' => '/maestrano/auth/saml/consume.php',
  'user_creation_mode'   => 'virtual'
));
