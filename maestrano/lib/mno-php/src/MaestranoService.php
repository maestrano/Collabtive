<?php

/**
 * Maestrano Service used to access all maestrano config variables
 *
 * These settings need to be filled in by the user prior to being used.
 */
class MaestranoService
{
  
  protected static $_instance;
  protected $after_sso_sign_in_path = '/';
  protected $settings;
  protected $client_session;
  
   /**
    * constructor
    *
    * this is private constructor (use getInstance to get an instance of this class)
    */
    private function __construct() {
      $this->settings = MnoSettings::getInstance();
    }
   
    /**
    * Returns an instance of this class
    * (this class uses the singleton pattern)
    *
    * @return MaestranoService
    */
    public static function getInstance()
    {
        if ( ! isset(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
  
  
   /**
    * Return the maestrano settings
    *
    * @return MnoSsoSession
    */
    public function getSettings()
    {
      return MnoSettings::getInstance();
    }
   
   /**
    * Return a reference to the user session object
    *
    * @return session hash
    */
   public function &getClientSession()
   {
     if (!$this->client_session) {
       $this->setClientSession($_SESSION);
     }
     
     return $this->client_session;
   }
   
   /**
    * Set internal pointer to the session
    *
    * @var session hash
    */
   public function setClientSession(& $session_hash)
   {
     return $this->client_session = & $session_hash;
   }
   
   /**
    * Return the maestrano sso session
    *
    * @return MnoSsoSession
    */
    public function getSsoSession()
    {
      return new MnoSsoSession();
    }
    
    /**
     * Check if Maestrano SSO is enabled
     *
     * @return boolean
     */
     public function isSsoEnabled()
     {
       return ($this->settings && $this->settings->sso_enabled);
     }
    
    /**
     * Return where the app should redirect internally to initiate
     * SSO request
     *
     * @return boolean
     */
    public function getSsoInitUrl()
    {
      return $this->settings->getAppSsoInitUrl();
    }
    
    /**
     * Return where the app should redirect after logging user
     * out
     *
     * @return string url
     */
    public function getSsoLogoutUrl()
    {
      return $this->settings->getSsoAccessLogoutUrl();
    }
    
    /**
     * Return where the app should redirect if user does
     * not have access to it
     *
     * @return string url
     */
    public function getSsoUnauthorizedUrl()
    {
      return $this->settings->getSsoAccessUnauthorizedUrl();
    }
    
    /**
     * Set the after sso signin path
     *
     * @return string url
     */
    public function setAfterSsoSignInPath($path)
    {
      $this->after_sso_sign_in_path = $path;
    }
    
    /**
     * Return the after sso signin path
     *
     * @return string url
     */
    public function getAfterSsoSignInPath()
    {
      if ($this->getClientSession()) {
				$session = $this->getClientSession();
				if (isset($session['mno_previous_url'])) {
					return $session['mno_previous_url'];
				}
        
			}
			return $this->after_sso_sign_in_path;
    }
  
}