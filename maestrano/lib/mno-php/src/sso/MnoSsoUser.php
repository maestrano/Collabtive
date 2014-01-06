<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class MnoSsoUser
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
  public $sso_session = '';
}