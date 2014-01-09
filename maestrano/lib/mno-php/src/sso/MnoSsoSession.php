<?php

/**
 * Helper class used to check the validity
 * of a Maestrano session
 */
class MnoSsoSession
{
  /**
   * Maestrano Settings object
   * @var MnoSettings
   */
  public $settings = null;
  
  /**
   * User UID
   * @var string
   */
  public $uid = '';
  
  /**
   * Maestrano SSO token
   * @var string
   */
  public $token = '';
  
  /**
   * When to recheck for validity of the sso session
   * @var datetime
   */
  public $recheck = null;
  
  /**
   * Construct the MnoSsoSession object
   *
   * @param MnoSettings $mno_settings
   *   A Maestrano Settings object
   * @param Array $session
   *   A session object, usually $_SESSION
   *
   */
  public function __construct(MnoSettings $mno_settings,$session)
  {
      // Populate attributes from params
      $this->settings = $mno_settings;
      $this->uid = $session['mno_uid'];
      $this->token = $session['mno_session'];
      $this->recheck = $session['mno_session_recheck'];
  }
  
  /**
   * Check whether we need to remotely check the
   * session or not
   *
   * @return boolean
   */
   public function remoteCheckRequired()
   {
     if ($this->uid && $this->token && $this->recheck) {
       if($this->recheck > (new DateTime('NOW'))) {
         return false;
       }
     }
     
     return true;
   }
   
   /**
    * Return the full url from which session check
    * should be performed
    *
    * @return string the endpoint url
    */
    public function sessionCheckUrl()
    {
      $url = $this->settings->sso_session_check_url . '/' . $this->uid . '?session=' . $this->token;
      return $url;
    }
}