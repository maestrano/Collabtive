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
      $this->assertEquals($session, $mno_session->session);
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
      // Build object via mocking (need stubbing for fetchUrl)
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => new DateTime());
      $mno_session = new MnoSsoSession($this->_mno_settings, $session);
      
      
      // Test return value
      $expected_url = $this->_mno_settings->sso_session_check_url . '/' . $session['mno_uid'] . '?session=' . $session['mno_session'];
      $this->assertEquals($expected_url, $mno_session->sessionCheckUrl());
    }
    
    public function testFunctionPerformRemoteCheckWhenValid() 
    {
      // Build object via mocking (need stubbing for fetchUrl)
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => new DateTime());
      $mno_session = $this->getMock('MnoSsoSession', array('fetchUrl'), array($this->_mno_settings, $session));
      
      // Stub remote content
      $remote_content = '{"valid":true,"recheck":"2014-01-09T03:36:15Z"}';
      $mno_session->expects($this->once())
                  ->method('fetchUrl')
                  ->will($this->returnValue($remote_content));
      
      // Test return value and recheck attribute
      $this->assertEquals(true,$mno_session->performRemoteCheck());
      $this->assertEquals(new DateTime('2014-01-09T03:36:15Z'),$mno_session->recheck);
    }
    
    public function testFunctionPerformRemoteCheckWhenInvalid() 
    {
      // Build object via mocking (need stubbing for fetchUrl)
      $session = array('mno_uid' => 'usr-xyz', 'mno_session' => '1dsf23sd', 'mno_session_recheck' => new DateTime());
      $mno_session = $this->getMock('MnoSsoSession', array('fetchUrl'), array($this->_mno_settings, $session));
      
      // Stub remote content
      $remote_content = '{"valid":false,"recheck":"2014-01-09T03:36:15Z"}';
      $mno_session->expects($this->once())
                  ->method('fetchUrl')
                  ->will($this->returnValue($remote_content));
      
      // Test return value and recheck attribute
      $recheck_before = $mno_session->recheck;
      $this->assertEquals(false,$mno_session->performRemoteCheck());
      $this->assertEquals($recheck_before, $mno_session->recheck);
    }
}