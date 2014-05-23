<?php
/**
 * This controller creates a SAML request and redirects to
 * Maestrano SAML Identity Provider
 *
 */

//-----------------------------------------------
// Define root folder
//-----------------------------------------------
require dirname(__FILE__) . '/../../app/initializers/auth_controllers.php';

// Build SAML request and Redirect to IDP
$url = Maestrano::sso()->buildRequest($_GET)->getRedirectUrl();

header("Location: $url");