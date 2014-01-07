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
   * When to recheck for validity of the sso session
   * @var datetime
   */
  public $sso_session_recheck = new DateTime('NOW');
}