<?php

// Class Test
class MnoSsoSessionTest extends PHPUnit_Framework_TestCase
{
    private $_mno_settings;

    public function setUp()
    {
      $settings = new MnoSettings();
      $settings->sso_session_check_url = 'http://localhost:3000/api/v1/auth/saml';
      $settings->sso_init_url = 'http://localhost:8888/maestrano/auth/saml/index.php';
      $this->_mno_settings = $settings;
    }
    
    public function testObjectInstantiation()
    {
      // Build object
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => time());
      $mno_session = new MnoSsoSession($this->_mno_settings, $session);
      
      // Test attributes
      $this->assertEquals($this->_mno_settings, $mno_session->settings);
      $this->assertEquals($session['mno_uid'], $mno_session->uid);
      $this->assertEquals($session['mno_session'], $mno_session->token);
      $this->assertEquals($session['mno_session_recheck'], $mno_session->recheck);
    }
    
    public function testFunctionRemoteCheckRequiredWhenRequired()
    {
      // Build object
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => new DateTime());
      $mno_session = new MnoSsoSession($this->_mno_settings, $session);
      
      // Test return value
      $this->assertEquals(true, $mno_session->remoteCheckRequired());
    }
    
    public function testFunctionRemoteCheckRequiredWhenNotRequired()
    {
      // Build object
      $future_date = new DateTime();
      $future_date->add(new DateInterval('P1D'));
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => $future_date);
      $mno_session = new MnoSsoSession($this->_mno_settings, $session);
      
      // Test return value
      $this->assertEquals(false, $mno_session->remoteCheckRequired());
    }
    
    public function testFunctionSessionCheckUrl()
    {
      // Build object
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => time());
      $mno_session = new MnoSsoSession($this->_mno_settings, $session);
      
      // Test return value
      $expected_url = $this->_mno_settings->sso_session_check_url . '/' . $session['mno_uid'] . '?session=' . $session['mno_session'];
      $this->assertEquals($expected_url, $mno_session->sessionCheckUrl());
    }
}