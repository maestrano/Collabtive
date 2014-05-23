<?php
/**
 * This controller creates a SAML request and redirects to
 * Maestrano SAML Identity Provider
 *
 */

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
define("MAESTRANO_ROOT", realpath(dirname(__FILE__) . '/../../'));

error_reporting(E_ALL);

require MAESTRANO_ROOT . '/app/initializers/auth_controllers.php';

// Build SAML request and Redirect to IDP
$authRequest = new Maestrano_Saml_AuthRequest(Maestrano::getSamlSettings());
$url = $authRequest->getRedirectUrl();

// Pass the group_id on 
if(array_key_exists('group_id', $_GET)) {
  $url .= "&group_id=" . $_GET['group_id'];
}

header("Location: $url");