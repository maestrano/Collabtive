<?php

// Tested on PHP 5.2, 5.3

// Check dependencies
if (!function_exists('curl_init')) {
  throw new Exception('Stripe needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Stripe needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('Stripe needs the Multibyte String PHP extension.');
}

// Maestrano Base files
require_once(dirname(__FILE__) . '/Maestrano/Maestrano.php');
require_once(dirname(__FILE__) . '/Maestrano/Settings.php');

// XMLSEC Libs
require_once(dirname(__FILE__) . '/Maestrano/Xmlseclibs/xmlseclibs.php');

// SAML
require_once(dirname(__FILE__) . '/Maestrano/Saml/AuthRequest.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/Response.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/Settings.php');
require_once(dirname(__FILE__) . '/Maestrano/Saml/XmlSec.php');

// SSO
require_once(dirname(__FILE__) . '/Maestrano/Sso/BaseUser.php');
require_once(dirname(__FILE__) . '/Maestrano/Sso/BaseGroup.php');
require_once(dirname(__FILE__) . '/Maestrano/Sso/Session.php');