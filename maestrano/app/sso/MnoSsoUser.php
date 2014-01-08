<?php

/**
 * Configure App specific behavior for 
 * Maestrano SSO
 */
class MnoSsoUser extends MnoSsoBaseUser
{
  /**
   * Database connection
   * @var PDO
   */
  public $connection = null;
  
  /**
   * Get the ID of a local user via Maestrano UID lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function _getLocalIdByUid()
  {
    $result = $this->connection->query("SELECT ID FROM user WHERE mno_uid = {$this->connection->quote($this->uid)} LIMIT 1")->fetch();
    
    if ($result && $result['ID']) {
      return $result['ID'];
    }
    
    return null;
  }
  
  /**
   * Get the ID of a local user via email lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function _getLocalIdByEmail()
  {
    $result = $this->connection->query("SELECT ID FROM user WHERE email = {$this->connection->quote($this->email)} LIMIT 1")->fetch();
    
    if ($result && $result['ID']) {
      return $result['ID'];
    }
    
    return null;
  }
  
  /**
   * Set the Maestrano UID on a local user via id lookup
   *
   * @return a user ID if found, null otherwise
   */
  protected function _setLocalUid()
  {
    throw new Exception('Function '. __FUNCTION__ . ' must be overriden in MnoSsoUser class!');
  }
}