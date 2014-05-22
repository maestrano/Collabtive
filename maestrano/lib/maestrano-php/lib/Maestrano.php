<?php

// Tested on PHP 5.2, 5.3

// Check dependencies
if (!function_exists('curl_init')) {
  throw new Exception('Maestrano needs the CURL PHP extension.');
}
if (!function_exists('json_decode')) {
  throw new Exception('Maestrano needs the JSON PHP extension.');
}
if (!function_exists('mb_detect_encoding')) {
  throw new Exception('Maestrano needs the Multibyte String PHP extension.');
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

// Util
require_once(dirname(__FILE__) . '/Maestrano/Util/Set.php');

// Api
require_once(dirname(__FILE__) . '/Maestrano/Api/Object.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Error.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/AttachedObject.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/ConnectionError.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/InvalidRequestError.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Requestor.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/Util.php');
require_once(dirname(__FILE__) . '/Maestrano/Api/AuthenticationError.php');

require_once(dirname(__FILE__) . '/Maestrano/Api/Resource.php');

// Billing
require_once(dirname(__FILE__) . '/Maestrano/Billing/Bill.php');