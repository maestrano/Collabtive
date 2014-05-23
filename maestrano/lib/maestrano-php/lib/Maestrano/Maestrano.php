<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class Maestrano
{
  
  /**
   * @var string|null The version of the Maestrano API to use for requests.
   */
  public static $apiVersion = null;

  /**
   * @var boolean Defaults to false.
   */
  public static $verifySslCerts = false;

  const VERSION = '0.1';

  protected static $_instance;
  protected static $after_sso_sign_in_path = '/';
  
  /**
   * Pointer to the current client session
   */
  protected static $client_session;
  
  /**
   * Maestrano environment
   * 'test' or 'production'
   * @var string
   */
  protected static $environment = 'production';
  
  /**
   * User creation strategy
   * 'real' or 'virtual'
   * - Use 'real' if your application allows a user to
   * be part of several groups.
   * - Use 'virtual' if your application can only allow
   * one group per user.
   * @var string
   */
  protected static $user_creation_mode = 'virtual';
  
  
  /**
   * Your application API token from
   * Maestrano or API Sandbox
   * @var string
   */
  protected static $api_key = 'some_long_token';
  
  /**
   * The host for this application
   * (Including HTTP protocol)
   */
  protected static $app_host = 'http://localhost:8888';
  
  /**
   * Is SSO enabled for this application
   * @var boolean
   */
  protected static $sso_enabled = true;
  
  /**
   * The app path where the SSO request should be initiated.
   * @var string
   */
  protected static $sso_app_init_path = '/maestrano/auth/saml/index.php';
  
  /**
   * The app path where the SSO request should be consumed.
   * @var string
   */
  protected static $sso_app_consume_path = '/maestrano/auth/saml/consume.php';
  
  
  /**
  * Return the Maestrano API Key
  */
  public static function getApiKey() {
    return self::$api_key;
  }

  /**
   * Return the Maestrano API Host
   */
   public static function getApiHost() {
     $host = self::$config[self::$environment]['api_host'];
     return $host;
   }
  
   /**
    * Return the user creation mode
    */
   public static function getUserCreationMode() {
     return self::$user_creation_mode;
   }
  
  /**
  * Return the maestrano settings
  *
  * @return Maestrano_Sso_Session
  */
  public static function configure($settings)
  {
    if (array_key_exists('api_key', $settings)) {
      self::$api_key = $settings['api_key'];
    } else {
      throw new ArgumentException('No api_key provided. Please add your API key.');
    }
    
    if (array_key_exists('sso_enabled', $settings)) {
      self::$sso_enabled = $settings['sso_enabled'];
    }
    
    if (array_key_exists('app_host', $settings)) {
      self::$app_host = $settings['app_host'];
    } else {
      trigger_error("No application host provided. Defaulting to: '" . self::$app_host . "'",E_USER_NOTICE);
    }
    
    if (array_key_exists('environment', $settings)) {
      self::$environment = $settings['environment'];
    } else {
      trigger_error("No environment provided. Defaulting to: '" . self::$environment . "'",E_USER_NOTICE);
    }
    
    if (array_key_exists('sso_app_init_path', $settings)) {
      self::$sso_app_init_path = $settings['sso_app_init_path'];
    }
    
    if (array_key_exists('sso_app_consume_path', $settings)) {
      self::$sso_app_consume_path = $settings['sso_app_consume_path'];
    }
    
    if (array_key_exists('user_creation_mode', $settings)) {
      self::$user_creation_mode = $settings['user_creation_mode'];
    }
    
    return true;
  }

  /**
  * Return a reference to the user session object
  *
  * @return session hash
  */
  public static function &getClientSession()
  {
   if (!self::$client_session) {
     self::setClientSession($_SESSION);
   }
 
   return self::$client_session;
  }

  /**
  * Set internal pointer to the session
  *
  * @var session hash
  */
  public static function setClientSession(& $session_hash)
  {
   return self::$client_session = & $session_hash;
  }

  /**
  * Return the maestrano sso session
  *
  * @return Maestrano_Sso_Session
  */
  public static function getSsoSession()
  {
    return new Maestrano_Sso_Session();
  }

  /**
   * Check if Maestrano SSO is enabled
   *
   * @return boolean
   */
   public static function isSsoEnabled()
   {
     return self::$sso_enabled;
   }

  /**
   * Return where the app should redirect internally to initiate
   * SSO request
   *
   * @return boolean
   */
  public static function getSsoInitUrl()
  {
    $host = self::$app_host;
    $path = self::$sso_app_init_path;
    return "${host}${path}";
  }

  /**
   * Return where the app should redirect after logging user
   * out
   *
   * @return string url
   */
  public static function getSsoLogoutUrl()
  {
    $host = self::$config[self::$environment]['api_host'];
    $endpoint = '/app_logout';
    
    return "${host}${endpoint}";
  }

  /**
   * Return where the app should redirect if user does
   * not have access to it
   *
   * @return string url
   */
  public static function getSsoUnauthorizedUrl()
  {
    $host = self::$config[self::$environment]['api_host'];
    $endpoint = '/app_access_unauthorized';
    
    return "${host}${endpoint}";
  }

  /**
   * Set the after sso signin path
   *
   * @return string url
   */
  public static function setAfterSsoSignInPath($path)
  {
    self::$after_sso_sign_in_path = $path;
  }

  /**
   * Return the after sso signin path
   *
   * @return string url
   */
  public static function getAfterSsoSignInPath()
  {
    if (self::getClientSession()) {
  		$session = self::getClientSession();
  		if (isset($session['mno_previous_url'])) {
  			return $session['mno_previous_url'];
  		}
    
  	}
  	return self::$after_sso_sign_in_path;
  }
  
  /**
   * Maestrano Single Sign-On processing URL
   * @var string
   */
  public static function getSsoIdpUrl() {
    $host = self::$config[self::$environment]['api_host'];
    $api_base = self::$config[self::$environment]['api_base'];
    $endpoint = 'auth/saml';
    return "${host}${api_base}${endpoint}";
  }
  
  /**
   * Specifies what format to use for SAML identification attribute 
   * (Maestrano user UID)
   * @var string
   */
  public static function getSsoNameIdFormat()
  {
    return self::$config[self::$environment]['sso_name_id_format'];
  }
  
  
  /**
   * The URL where the SSO response will be posted and consumed.
   * @var string
   */
  public static function getAppSsoConsumeUrl()
  {
    $host = self::$app_host;
    $path = self::$sso_app_consume_path;
    return "${host}${path}";
  }
  
  /**
   * The x509 certificate used to authenticate the request.
   * @var string
   */
  public static function getSsoX509Certificate()
  {
    return self::$config[self::$environment]['sso_x509_certificate'];
  }
  
  /**
   * The Maestrano endpoint in charge of providing session information
   * @var string
   */
  public static function getSsoSessionCheckUrl($user_id,$sso_session) 
  {
    $host = self::$config[$self::$environment]['api_host'];
    $api_base = self::$config[$self::$environment]['api_base'];
    $endpoint = 'auth/saml';
    
    return "${host}${api_base}${endpoint}/${user_id}?session=${sso_session}";
  }
  
  /**
   * Return a settings object for php-saml
   * 
   * @return Maestrano_Saml_Settings
   */
  public static function getSamlSettings() {
    $settings = new Maestrano_Saml_Settings();
    
    // Configure SAML
    $settings->idpSingleSignOnUrl = self::getSsoIdpUrl();
    $settings->idpPublicCertificate = self::getSsoX509Certificate();
    $settings->spReturnUrl = self::getAppSsoConsumeUrl();
    $settings->spIssuer = self::getApiKey();
    $settings->requestedNameIdFormat = self::getSsoNameIdFormat();
    
    return $settings;
  }
  
  /* 
   * Environment related configuration 
   */
  private static $config = array(
    'test' => array(
      'api_host'               => 'http://api-sandbox.maestrano.io',
      'api_base'               => '/api/v1/',
      'sso_name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso_x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5\nWhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu\nP2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I\n5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2\n28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo\neHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ\njYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc\nMPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc\n2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES\nRkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==\n-----END CERTIFICATE-----"
    ),
    'production' => array(
      'api_host'               => 'https://maestrano.com',
      'api_base'               => '/api/v1/',
      'sso_name_id_format'     => Maestrano_Saml_Settings::NAMEID_PERSISTENT,
      'sso_x509_certificate'   => "-----BEGIN CERTIFICATE-----\nMIIDezCCAuSgAwIBAgIJAPFpcH2rW0pyMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD\nVQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT\nEU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ\nKoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyNDEw\nWhcNMzMxMjMwMDUyNDEwWjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP\nMA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG\nA1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz\ndHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQD3feNNn2xfEz5/\nQvkBIu2keh9NNhobpre8U4r1qC7h7OeInTldmxGL4cLHw4ZAqKbJVrlFWqNevM5V\nZBkDe4mjuVkK6rYK1ZK7eVk59BicRksVKRmdhXbANk/C5sESUsQv1wLZyrF5Iq8m\na9Oy4oYrIsEF2uHzCouTKM5n+O4DkwIDAQABo4HuMIHrMB0GA1UdDgQWBBSd/X0L\n/Pq+ZkHvItMtLnxMCAMdhjCBuwYDVR0jBIGzMIGwgBSd/X0L/Pq+ZkHvItMtLnxM\nCAMdhqGBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE\nBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN\nbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u\nY29tggkA8WlwfatbSnIwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQDE\nhe/18oRh8EqIhOl0bPk6BG49AkjhZZezrRJkCFp4dZxaBjwZTddwo8O5KHwkFGdy\nyLiPV326dtvXoKa9RFJvoJiSTQLEn5mO1NzWYnBMLtrDWojOe6Ltvn3x0HVo/iHh\nJShjAn6ZYX43Tjl1YXDd1H9O+7/VgEWAQQ32v8p5lA==\n-----END CERTIFICATE-----"
    )
  );
}