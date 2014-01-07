<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class MnoSsoBaseUser
{
  /**
   * User UID
   * @var string
   */
  public $uid = '';
  
  /**
   * User email
   * @var string
   */
  public $email = '';
  
  /**
   * User name
   * @var string
   */
  public $name = '';
  
  /**
   * User surname
   * @var string
   */
  public $surname = '';
  
  /**
   * Maestrano specific user sso session token
   * @var string
   */
  public $sso_session = '';
  
  /**
   * When to recheck for validity of the sso session
   * @var datetime
   */
  public $sso_session_recheck = null;
  
  /**
   * Is user owner of the app
   * @var boolean
   */
  public $app_owner = false;
  
  /**
   * An associative array containing the Maestrano 
   * organizations using this app and to which the
   * user belongs.
   * Keys are the maestrano organization uid.
   * Values are an associative array containing the
   * name of the organization as well as the role 
   * of the user within that organization.
   * ---
   * e.g:
   * { 'org-876' => {
   *      'name' => 'SomeOrga',
   *      'role' => 'Super Admin'
   *   }
   * }
   * @var array
   */
  public $organizations = array();
  
  /**
   * User Local Id
   * @var string
   */
  public $local_id = '';
  
  
  /**
   * Construct the MnoSsoBaseUser object from a SAML response
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response)
  {
      // First get the assertion attributes from the SAML
      // response
      $assert_attrs = $saml_response->getAttributes();
      
      // Populate user attributes from assertions
      $this->uid = $assert_attrs['mno_uid'][0];
      $this->sso_session = $assert_attrs['mno_session'][0];
      $this->sso_session_recheck = new DateTime($assert_attrs['mno_session_recheck'][0]);
      $this->name = $assert_attrs['name'][0];
      $this->surname = $assert_attrs['surname'][0];
      $this->email = $assert_attrs['email'][0];
      $this->app_owner = $assert_attrs['app_owner'][0];
      $this->organizations = json_decode($assert_attrs['organizations'][0],true);
  }
}