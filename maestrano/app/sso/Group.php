<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO Group matching and creation
 *
 * Summary of attributes available:
 * ================================
 * Group related information
 * --------------------------
 *  -> uid: universal id of group
 *  -> free_trial_end_at: end of free trial (only applicable if you accept free trials)
 *  -> country: (user) country in alpha2 format
 *  -> company_name: (user) company name (not a mandatory field - might be blank)
 */
class Maestrano_Sso_Group extends Maestrano_Sso_BaseGroup
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
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
  }
  
  /**
   * Get the ID of a local group via Maestrano UID lookup
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return a group ID if found, null otherwise
   */
  protected function getLocalIdByUid()
  {
    $result = $this->connection->query("SELECT ID FROM projekte WHERE mno_uid = {$this->connection->quote($this->uid)} LIMIT 1")->fetch();
    
    if ($result && $result['ID']) {
      return $result['ID'];
    }
    
    return null;
  }
  
  /**
   * Create a local group based on the sso user
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return a group ID if created, null otherwise
   */
  protected function createLocalGroup()
  {
    $lid = null;
    
    // First set $conn variable (used internally by collabtive methods)
    $conn = $this->connection;
    
    // Create group
    $group = new project();
    $attr = $this->buildLocalGroup();
    $lid = $group->add($attr['name'], $attr['description'], $attr['end_date'], $attributes['budget']);
    
    return $lid;
  }
  
  protected function buildLocalGroup()
  {
    $attributes = array();
    if ($this->company_name && $this->company_name != '') {
      $attributes['name'] = $this->company_name . '(' . $this->uid . ')';
    } else {
      $attributes['name'] = "Project " . $this->uid;
    }
    
    $attributes['description'] = "";
    
    $end_date = new DateTime();
    $end_date->add(new DateInterval('P1Y'));
    $attributes['end_date'] = $end_date;
    $attributes['budget'] = 0;
    
    return $attributes;
  }
  
  /**
   * Add a user to an existing group if the user is not
   * part of it already
   */
  public function addUser($sso_user,$user_role) {
    
    // First set $conn variable (used internally by collabtive methods)
    $conn = $this->connection;
    
    $group = new project();
    return $group->assign($sso_user->local_id, $this->local_id);
  }
  
  
  /**
   * Set the Maestrano UID on a local group
   * This method must be re-implemented in Maestrano_Sso_Group
   * (raise an error otherwise)
   *
   * @return boolean
   */
  protected function setLocalUid()
  {
    if($this->local_id) {
      $upd = $this->connection->query("UPDATE projekte SET mno_uid = {$this->connection->quote($this->uid)} WHERE ID = $this->local_id");
      return $upd;
    }
    
    return false;
  }
  
}