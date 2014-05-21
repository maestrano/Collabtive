<?php

/**
 * Properly format a User received from Maestrano 
 * SAML IDP
 */
class MnoSsoBaseGroup
{
  /* User UID */
  public $uid = '';
  
  /* When does free trial terminate */
  public $free_trial_end_at = '';
  
  /* Group Local Id */
  public $local_id = null;
  
  
  /**
   * Construct the MnoSsoBaseUser object from a SAML response
   *
   * @param OneLogin_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(OneLogin_Saml_Response $saml_response)
  {
      // Get maestrano service, assertion attributes and session
      $mno_service = MaestranoService::getInstance();
      $assert_attrs = $saml_response->getAttributes();
      $this->session = $mno_service->getClientSession();
      
      // Extract session information
      $this->uid = $assert_attrs['group_uid'][0];
      $this->free_trial_end_at = new DateTime($assert_attrs['group_end_free_trial'][0]);
  }
  
  /**
   * Try to find a local group matching the sso one
   * using uid.
   * ---
   * Internally use the following interface methods:
   *  - getLocalIdByUid
   * 
   * @return local_id if a local user matched, null otherwise
   */
  public function matchLocal()
  {
    // Try to get the local id from uid
    $this->local_id = $this->getLocalIdByUid();
    
    return $this->local_id;
  }
  
  /**
   * Create a local group (global customer account) by invoking createLocalUser
   * and set uid on the newly created user
   * If createLocalUser returns null then access
   * is refused to the user
   */
   public function createLocalGroupOrDenyAccess()
   {
     if (is_null($this->local_id)) {
       $this->local_id = $this->createLocalUser();

        // If a user has been created successfully
        // then make sure UID is set on it
        if ($this->local_id) {
          $this->setLocalUid();
        }
     }
     
     return $this->local_id;
   }
  
  /**
   * Create a local user based on the sso user
   * This method must be re-implemented in MnoSsoGroup
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function createLocalGroup()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoSsoGroup class!');
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   * This method must be re-implemented in MnoSsoGroup
   * (raise an error otherwise)
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoSsoGroup class!');
  }
  
}