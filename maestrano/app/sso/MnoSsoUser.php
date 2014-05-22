<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO User matching and creation
 *
 * Summary of attributes available:
 * ================================
 * Group related information
 * --------------------------
 *  -> group_uid: universal id of group the user is logging in via
 *  -> group_role: role of user within the above group
 *
 * User identification:
 * --------------------
 * You should set Maestrano_Settings#user_creation_mode to 'real' or 'virtual'
 * depending on whether your users can be part of multiple groups or not
 * and then use the getUid() and getEmail() methods.
 * Use the attributes below only if you know what you're doing
 *  -> uid: user maestrano id
 *  -> virtual_uid: truly unique maestrano uid across users and groups
 *  -> email: email address of the user
 *  -> virtual_email: truly unique maestrano email address across users and groups
 *
 *
 * User metadata
 * --------------
 *  -> name: user first name
 *  -> surname: user last name
 *  -> country: user country in alpha2 format
 *  -> company_name: user company name (not a mandatory field - might be blank)
 */
class MnoSsoUser extends Maestrano_Sso_BaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  /**
   * Collabtive user object
   * @var PDO
   */
  public $_user = null;
  
  /**
   * Collabtive roles object
   * @var PDO
   */
  public $_roles = null;
  
  
  /**
   * Extend constructor to inialize app specific objects
   *
   * @param Maestrano_Saml_Response $saml_response
   *   A SamlResponse object from Maestrano containing details
   *   about the user being authenticated
   */
  public function __construct(Maestrano_Saml_Response $saml_response, $opts = array())
  {
    // Call Parent
    parent::__construct($saml_response);
    
    // Assign new attributes
    $this->connection = $opts['db_connection'];
    $this->_user = new user();
    $this->_roles = new roles();
  }
  
  
  /**
   * Sign the user in the application. 
   * Parent method deals with putting the mno_uid, 
   * mno_session and mno_session_recheck in session.
   *
   * @return boolean whether the user was successfully set in session or not
   */
  protected function setInSession()
  {
    // First set $conn variable (used internally by collabtive methods)
    $conn = $this->connection;
    
    $sel1 = $conn->query("SELECT ID,name,locale,lastlogin,gender FROM user WHERE ID = $this->local_id");
    $chk = $sel1->fetch();
    if ($chk["ID"] != "") {
        $now = time();
        
        // Set session
        $this->session['userid'] = $chk['ID'];
        $this->session['username'] = stripslashes($chk['name']);
        $this->session['lastlogin'] = $now;
        $this->session['userlocale'] = $chk['locale'];
        $this->session['usergender'] = $chk['gender'];
        $this->session["userpermissions"] = $this->_roles->getUserRole($chk["ID"]);
        
        // Update last login timestamp
        $upd1 = $conn->query("UPDATE user SET lastlogin = '$now' WHERE ID = $this->local_id");
        
        return true;
    } else {
        return false;
    }
  }
  
  
  /**
   * Used by createLocalUserOrDenyAccess to create a local user 
   * based on the sso user.
   * If the method returns null then access is denied
   *
   * @return the ID of the user created, null otherwise
   */
  protected function createLocalUser()
  {
    $lid = null;
    
    // First set $conn variable (used internally by collabtive methods)
    $conn = $this->connection;
    
    // Create user
    $lid = $this->_user->add($this->getEmail(), $this->getEmail(), '', $this->generatePassword());
    
    // Create role for new user
    if ($lid) {
      $this->_roles->assign($this->getRoleToAssign(), $lid);
    }
    
    return $lid;
  }
  
  /**
   * Get application wide role to give to the user based on context
   * This step should not be required for a cloud application as role
   * should be set at the "User <-> Group" relation level
   *
   * @return the ID of the role, null otherwise
   */
  public function getRoleToAssign() {
    $role_id = 2; // User
    
    if ($this->group_role == "Admin" || $this->group_role == "Super Admin") {
      $role_id = 1; // Admin
    }
    
    return $role_id;
  }
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $result = $this->connection->query("SELECT ID FROM user WHERE mno_uid = {$this->connection->quote($this->getUid())} LIMIT 1")->fetch();
    
    if ($result && $result['ID']) {
      return $result['ID'];
    }
    
    return null;
  }
  
  /**
   * Set all 'soft' details on the user (like name, surname, email)
   * Implementing this method is optional.
   *
   * @return boolean whether the user was synced or not
   */
   protected function syncLocalDetails()
   {
     if($this->local_id) {
       $upd = $this->connection->query("UPDATE user SET name = {$this->connection->quote($this->getEmail())}, email = {$this->connection->quote($this->getEmail())} WHERE ID = $this->local_id");
       return $upd;
     }
     
     return false;
   }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $upd = $this->connection->query("UPDATE user SET mno_uid = {$this->connection->quote($this->getUid())} WHERE ID = $this->local_id");
      return $upd;
    }
    
    return false;
  }
}