<?php
/**
 * SAMPLE Code to demonstrate how provide SAML settings.
 *
 * The settings are contained within a OneLogin_Saml_Settings object. You need to
 * provide, at a minimum, the following things:
 *
 *  - idpSingleSignOnUrl
 *    This is the URL to forward to for auth requests.
 *    It will be provided by your IdP.
 *
 *  - idpPublicCertificate
 *    This is a certificate required to authenticate your request.
 *    This certificate should be provided by your IdP.
 * 
 *  - spReturnUrl
 *    The URL that the IdP should redirect to once the authorization is complete.
 *    You must provide this, and it should point to the consume.php script or its equivalent.
 */

define('XMLSECLIBS_DIR', './../../lib/php-saml/ext/xmlseclibs/');
require XMLSECLIBS_DIR . 'xmlseclibs.php';

define('ONELOGIN_SAML_DIR', './../../lib/php-saml/src/OneLogin/Saml/');
require ONELOGIN_SAML_DIR . 'AuthRequest.php';
require ONELOGIN_SAML_DIR . 'Response.php';
require ONELOGIN_SAML_DIR . 'Settings.php';
require ONELOGIN_SAML_DIR . 'XmlSec.php';

$settings = new OneLogin_Saml_Settings();

// When using Service Provider Initiated SSO (starting at index.php), this URL asks the IdP to authenticate the user.
$settings->idpSingleSignOnUrl = 'http://localhost:3000/api/v1/auth/saml';

// The certificate for the users account in the IdP
$settings->idpPublicCertificate = '01:06:15:89:25:7d:78:12:28:a6:69:c7:de:63:ed:74:21:f9:f5:36';
// $settings->idpPublicCertificate = <<<CERTIFICATE
// -----BEGIN CERTIFICATE-----
// MIIBrTCCAaGgAwIBAgIBATADBgEAMGcxCzAJBgNVBAYTAlVTMRMwEQYDVQQIDApD
// YWxpZm9ybmlhMRUwEwYDVQQHDAxTYW50YSBNb25pY2ExETAPBgNVBAoMCE9uZUxv
// Z2luMRkwFwYDVQQDDBBhcHAub25lbG9naW4uY29tMB4XDTEwMDMwOTA5NTgzNFoX
// DTE1MDMwOTA5NTgzNFowZzELMAkGA1UEBhMCVVMxEzARBgNVBAgMCkNhbGlmb3Ju
// aWExFTATBgNVBAcMDFNhbnRhIE1vbmljYTERMA8GA1UECgwIT25lTG9naW4xGTAX
// BgNVBAMMEGFwcC5vbmVsb2dpbi5jb20wgZ8wDQYJKoZIhvcNAQEBBQADgY0AMIGJ
// AoGBANtmwriqGBbZy5Dwy2CmJEtHEENVPoATCZP3UDESRDQmXy9Q0Kq1lBt+KyV4
// kJNHYAAQ9egLGWQ8/1atkPBye5s9fxROtf8VO3uk/x/X5VSRODIrhFISGmKUnVXa
// UhLFIXkGSCAIVfoR5S2ggdfpINKUWGsWS/lEzLNYMBkURXuVAgMBAAEwAwYBAAMB
// AA==
// -----END CERTIFICATE-----
// CERTIFICATE;

// The URL where to the SAML Response/SAML Assertion will be posted
$settings->spReturnUrl = 'http://localhost:8888/maestrano/auth/saml/consume.php';

// Name of this application
$settings->spIssuer = 'bla.app.dev.maestrano.io';

// Tells the IdP to return the email address of the current user
$settings->requestedNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
//$settings->requestedNameIdFormat = OneLogin_Saml_Settings::NAMEID_EMAIL_ADDRESS;

return $settings;