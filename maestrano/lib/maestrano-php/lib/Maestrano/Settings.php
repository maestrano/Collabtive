<?php

/**
 * Holds Maestrano settings
 *
 * These settings need to be filled in by the user prior to being used.
 */
class Maestrano_Settings
{
    /* Singleton instance */
    protected static $_instance;
    
    /**
     * Maestrano environment
     * 'test' or 'production'
     * @var string
     */
    public $environment = 'test';
    
    /**
     * User creation strategy
     * 'real' or 'virtual'
     * - Use 'real' if your application allows a user to
     * be part of several groups.
     * - Use 'virtual' if your application can only allow
     * one group per user.
     * @var string
     */
    public $user_creation_mode = 'virtual';
    
    
    /**
     * Your application API token from
     * Maestrano or API Sandbox
     * @var string
     */
    public $api_token = 'some_long_token';
    
    /**
     * The host for this application
     * (Including HTTP protocol)
     */
    public $app_host = 'http://localhost:8888';
    
    /**
     * Is SSO enabled for this application
     * @var boolean
     */
    public $sso_enabled = false;
    
    /**
     * The app path where the SSO request should be initiated.
     * @var string
     */
    public $sso_app_init_path = '';
    
    /**
     * The app path where the SSO request should be consumed.
     * @var string
     */
    public $sso_app_consume_path = '';
    
    /**
     * Specifies what format to use for SAML identification token 
     * (Maestrano user UID)
     * @var string
     */
    public function getSsoNameIdFormat()
    {
      $this->config[$this->environment]['sso_name_id_format'];
    }
    
    /**
     * Maestrano Single Sign-On processing URL
     * @var string
     */
    public function getSsoIdpUrl() {
      $host = $this->config[$this->environment]['api_host'];
      $api_base = $this->config[$this->environment]['api_base'];
      $endpoint = 'auth/saml';
      return "${host}${api_base}${endpoint}";
    }
    
    /**
     * The URL where the SSO handshake will be initiated
     * @var string
     */
    public function getAppSsoInitUrl()
    {
      $host = $this->app_host;
      $path = $this->sso_app_init_path;
      return "${host}${path}";
    }
    
    /**
     * The URL where the SSO response will be posted and consumed.
     * @var string
     */
    public function getAppSsoConsumeUrl()
    {
      $host = $this->app_host;
      $path = $this->sso_app_consume_path;
      return "${host}${path}";
    }
    
    /**
     * The URL where the application should redirect if
     * user is not given access.
     * @var string
     */
    public function getSsoAccessUnauthorizedUrl()
    {
      $host = $this->config[$this->environment]['api_host'];
      $endpoint = '/app_access_unauthorized';
      
      return "${host}${endpoint}";
    }
    
    /**
     * The URL where the application should redirect when
     * user logs out
     * @var string
     */
    public function getSsoAccessLogoutUrl() 
    {
      $host = $this->config[$this->environment]['api_host'];
      $endpoint = '/app_logout';
      
      return "${host}${endpoint}";
    }
    
    /**
     * The x509 certificate used to authenticate the request.
     * @var string
     */
    public function getSsoX509Certificate()
    {
      return $this->config[$this->environment]['sso_x509_certificate'];
    }
    
    /**
     * The Maestrano endpoint in charge of providing session information
     * @var string
     */
    public function getSsoSessionCheckUrl($user_id,$sso_session) 
    {
      $host = $this->config[$this->environment]['api_host'];
      $api_base = $this->config[$this->environment]['api_base'];
      $endpoint = 'auth/saml';
      
      return "${host}${api_base}${endpoint}/${user_id}?session=${sso_session}";
    }
    
    /**
     * Return the base API url - with trailing slash
     */
    public function getApiUrl()
    {
      $host = $this->config[$this->environment]['api_host'];
      $api_base = $this->config[$this->environment]['api_base'];
      
      return "${host}${api_base}";
    }
    
    /**
     * Returns an instance of this class
     * (this class uses the singleton pattern)
     *
     * @return Maestrano_Settings instance
     */
    public static function getInstance()
    {
        if ( ! isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    /**
     * Return a settings object for php-saml
     * 
     * @return Maestrano_Saml_Settings
     */
    public function getSamlSettings() {
      $settings = new Maestrano_Saml_Settings();
      
      // Configure SAML
      $settings->idpSingleSignOnUrl = $this->getSsoIdpUrl();
      $settings->idpPublicCertificate = $this->getSsoX509Certificate();
      $settings->spReturnUrl = $this->getAppSsoConsumeUrl();
      $settings->spIssuer = $this->api_token;
      $settings->requestedNameIdFormat = $this->getSsoNameIdFormat();
      
      return $settings;
    }
    
    
    /* 
     * Environment related configuration 
     */
    private $config = array(
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