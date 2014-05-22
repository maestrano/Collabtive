<?php
$mno_settings = Maestrano::getInstance()->getSettings();

// Set environment
// 'test' or 'production'
$mno_settings->environment = 'test';

/**
 * User creation strategy: 'real' or 'virtual'
 *
 * Use 'real' if your application allows a user to
 * be part of several groups.
 *
 * Use 'virtual' if your application can only allow
 * one group per user.
 */
$mno_settings->user_creation_mode = 'virtual';


//---------------------------------------------
// Set configuration based on environment
//---------------------------------------------
if ($mno_settings->environment == 'production') {
  // Enable Maestrano SSO for this app
  $mno_settings->sso_enabled = true;
  
  // Set your application host
  $mno_settings->app_host = "https://myservice.com";

  // API Token (obtained on maestrano.com)
  $mno_settings->api_token = 'production_token_from_maestrano';

  // SSO initialization URL
  $mno_settings->sso_app_init_path = '/maestrano/auth/saml/index.php';

  // SSO processing url
  $mno_settings->sso_app_consume_path = '/maestrano/auth/saml/consume.php';

} else {
  // Enable Maestrano SSO for this app
  $mno_settings->sso_enabled = true;
  
  // Set your application host
  $mno_settings->app_host = "http://localhost:8888";

  // API Token (obtained on api-sandbox.maestrano.io)
  $mno_settings->api_token = 'gfcmbu8269wyi0hjazk4t7o1sndpvrqxl53e1';

  // SSO initialization URL
  $mno_settings->sso_app_init_path = '/maestrano/auth/saml/index.php';

  // SSO processing url
  $mno_settings->sso_app_consume_path = '/maestrano/auth/saml/consume.php';
}



