<?php

// Class helper for database connection
class PDOMock extends PDO {
    public function __construct() {}
    
    // Make it final to avoid stubbing
    public final function quote($arg)
    {
      return "'$arg'";
    }
}

// Class Test
class MnoSsoUserTest extends PHPUnit_Framework_TestCase
{
    private $_saml_settings;
    
    public function setUp()
    {
      parent::setUp();
      
      // Create SESSION
      $_SESSION = array();
      
      $settings = new OneLogin_Saml_Settings;
      $settings->idpSingleSignOnUrl = 'http://localhost:3000/api/v1/auth/saml';

      // The certificate for the users account in the IdP
      $settings->idpPublicCertificate = <<<CERTIFICATE
-----BEGIN CERTIFICATE-----
MIIDezCCAuSgAwIBAgIJAOehBr+YIrhjMA0GCSqGSIb3DQEBBQUAMIGGMQswCQYD
VQQGEwJBVTEMMAoGA1UECBMDTlNXMQ8wDQYDVQQHEwZTeWRuZXkxGjAYBgNVBAoT
EU1hZXN0cmFubyBQdHkgTHRkMRYwFAYDVQQDEw1tYWVzdHJhbm8uY29tMSQwIgYJ
KoZIhvcNAQkBFhVzdXBwb3J0QG1hZXN0cmFuby5jb20wHhcNMTQwMTA0MDUyMjM5
WhcNMzMxMjMwMDUyMjM5WjCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEP
MA0GA1UEBxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQG
A1UEAxMNbWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVz
dHJhbm8uY29tMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDVkIqo5t5Paflu
P2zbSbzxn29n6HxKnTcsubycLBEs0jkTkdG7seF1LPqnXl8jFM9NGPiBFkiaR15I
5w482IW6mC7s8T2CbZEL3qqQEAzztEPnxQg0twswyIZWNyuHYzf9fw0AnohBhGu2
28EZWaezzT2F333FOVGSsTn1+u6tFwIDAQABo4HuMIHrMB0GA1UdDgQWBBSvrNxo
eHDm9nhKnkdpe0lZjYD1GzCBuwYDVR0jBIGzMIGwgBSvrNxoeHDm9nhKnkdpe0lZ
jYD1G6GBjKSBiTCBhjELMAkGA1UEBhMCQVUxDDAKBgNVBAgTA05TVzEPMA0GA1UE
BxMGU3lkbmV5MRowGAYDVQQKExFNYWVzdHJhbm8gUHR5IEx0ZDEWMBQGA1UEAxMN
bWFlc3RyYW5vLmNvbTEkMCIGCSqGSIb3DQEJARYVc3VwcG9ydEBtYWVzdHJhbm8u
Y29tggkA56EGv5giuGMwDAYDVR0TBAUwAwEB/zANBgkqhkiG9w0BAQUFAAOBgQCc
MPgV0CpumKRMulOeZwdpnyLQI/NTr3VVHhDDxxCzcB0zlZ2xyDACGnIG2cQJJxfc
2GcsFnb0BMw48K6TEhAaV92Q7bt1/TYRvprvhxUNMX2N8PHaYELFG2nWfQ4vqxES
Rkjkjqy+H7vir/MOF3rlFjiv5twAbDKYHXDT7v1YCg==
-----END CERTIFICATE-----
CERTIFICATE;

      // The URL where to the SAML Response/SAML Assertion will be posted
      $settings->spReturnUrl = 'http://localhost:8888/maestrano/auth/saml/consume.php';

      // Name of this application
      $settings->spIssuer = 'bla.app.dev.maestrano.io';

      // Tells the IdP to return the email address of the current user
      $settings->requestedNameIdFormat = 'urn:oasis:names:tc:SAML:2.0:nameid-format:persistent';
      
      $this->_saml_settings = $settings;
    }
    
    // Used to test protected methods
    protected static function getMethod($name) {
      $class = new ReflectionClass('MnoSsoUser');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }
    
    public function testFunctionGetLocalIdByUid()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('_getLocalIdByUid');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a statement stub
      $stmt_stub = $this->getMock('PDOStatement');
      $stmt_stub->expects($this->once())
                ->method('fetch')
                ->will($this->returnValue(array("ID" => $expected_id)));
      
      // Create a connection stub
      $pdo_stub = $this->getMock('PDOMock');
      $pdo_stub->expects($this->once())
               ->method('query')
               ->with($this->equalTo("SELECT ID FROM user WHERE mno_uid = '$sso_user->uid' LIMIT 1"))
               ->will($this->returnValue($stmt_stub));
               
      
      // Test method returns the right id
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionGetLocalIdByEmail()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('_getLocalIdByEmail');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a statement stub
      $stmt_stub = $this->getMock('PDOStatement');
      $stmt_stub->expects($this->once())
                ->method('fetch')
                ->will($this->returnValue(array("ID" => $expected_id)));
      
      // Create a connection stub
      $pdo_stub = $this->getMock('PDOMock');
      $pdo_stub->expects($this->once())
               ->method('query')
               ->with($this->equalTo("SELECT ID FROM user WHERE email = '$sso_user->email' LIMIT 1"))
               ->will($this->returnValue($stmt_stub));
               
      
      // Test method returns the right id
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionSetLocalUid()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('_setLocalUid');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = 1234;
      
      // Create a connection stub
      $pdo_stub = $this->getMock('PDOMock');
      $pdo_stub->expects($this->once())
               ->method('query')
               ->with($this->equalTo("UPDATE user SET mno_uid = '$sso_user->uid' WHERE ID = $sso_user->local_id"))
               ->will($this->returnValue(true));
               
      
      // Test method returns true
      $sso_user->connection = $pdo_stub;
      $this->assertEquals(true,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionCreateLocalUserWhenAppOwner()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('_createLocalUser');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = true;
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a user stub
      $sso_user->_user = $this->getMock('user');
      $sso_user->_user->expects($this->once())
               ->method('add')
               ->with($this->equalTo("$sso_user->name $sso_user->surname"), $this->equalTo($sso_user->email), $this->equalTo(''), $this->equalTo('123456789'))
               ->will($this->returnValue($expected_id));
               
     // Create a roles stub
     $sso_user->_roles = $this->getMock('roles');
     $sso_user->_roles->expects($this->once())
              ->method('assign')
              ->with($this->equalTo(1),$this->equalTo($expected_id))
              ->will($this->returnValue(true));


      // Test method returns the right id
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionCreateLocalUserWhenOrgaAdmin()
    {
      
      // Specify which protected method get tested
      $protected_method = self::getMethod('_createLocalUser');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = false;
      $sso_user->organizations = array('org-xyz' => array('name' => 'MyOrga', 'role' => 'Admin'));
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a user stub
      $sso_user->_user = $this->getMock('user');
      $sso_user->_user->expects($this->once())
               ->method('add')
               ->with($this->equalTo("$sso_user->name $sso_user->surname"), $this->equalTo($sso_user->email), $this->equalTo(''), $this->equalTo('123456789'))
               ->will($this->returnValue($expected_id));
               
      // Create a roles stub
      $sso_user->_roles = $this->getMock('roles');
      $sso_user->_roles->expects($this->once())
              ->method('assign')
              ->with($this->equalTo(1),$this->equalTo($expected_id))
              ->will($this->returnValue(true));


      // Test method returns the right id
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionCreateLocalUserWhenNormal()
    {
      // Specify which protected method get tested
      $protected_method = self::getMethod('_createLocalUser');
      
      // Build User
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion));
      $sso_user->local_id = null;
      $sso_user->app_owner = false;
      $sso_user->organizations = array('org-xyz' => array('name' => 'MyOrga', 'role' => 'Member'));
      
      // Set expected_id
      $expected_id = 1234;
      
      // Create a user stub
      $sso_user->_user = $this->getMock('user');
      $sso_user->_user->expects($this->once())
               ->method('add')
               ->with($this->equalTo("$sso_user->name $sso_user->surname"), $this->equalTo($sso_user->email), $this->equalTo(''), $this->equalTo('123456789'))
               ->will($this->returnValue($expected_id));
               
      // Create a roles stub
      $sso_user->_roles = $this->getMock('roles');
      $sso_user->_roles->expects($this->once())
              ->method('assign')
              ->with($this->equalTo(2),$this->equalTo($expected_id))
              ->will($this->returnValue(true));
      
      
      // Test method returns the right id
      $sso_user->connection = $pdo_stub;
      $this->assertEquals($expected_id,$protected_method->invokeArgs($sso_user,array()));
    }
    
    public function testFunctionSignIn()
    {
      // Build User
      $session = array();
      $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
      $sso_user = new MnoSsoUser(new OneLogin_Saml_Response($this->_saml_settings, $assertion),$session);
      $sso_user->local_id = 1234;
      
      // Create a roles stub
      $sso_user->_roles = $this->getMock('roles');
      $sso_user->_roles->expects($this->once())
               ->method('getUserRole')
               ->with($this->equalTo($sso_user->local_id))
               ->will($this->returnValue(1));
      
      // Create a statement stub
      $last_login = strtotime('-1 day');
      $stmt_stub = $this->getMock('PDOStatement');
      $stmt_stub->expects($this->once())
                ->method('fetch')
                ->will($this->returnValue(array(
                  "ID" => $sso_user->local_id, 
                  "name" => "$sso_user->name $sso_user->surname",
                  "locale" => '',
                  "lastlogin" => $last_login,
                  "gender" => ''
                  )));
      
      // Create a connection stub
      $pdo_stub = $this->getMock('PDOMock');
      $pdo_stub->expects($this->exactly(2))
               ->method('query')
               ->with($this->logicalOr(
                        $this->equalTo("SELECT ID,name,locale,lastlogin,gender FROM user WHERE ID = $sso_user->local_id"),
                        $this->logicalAnd(
                          $this->stringContains("UPDATE user SET lastlogin ="),
                          $this->stringContains("WHERE ID = $sso_user->local_id")
                        )
                      ))
               ->will($this->returnValue($stmt_stub));
               
      // Test session variables
      $sso_user->connection = $pdo_stub;
      $sso_user->signIn();
      
      $this->assertEquals($sso_user->local_id, $session['userid']);
      $this->assertEquals("$sso_user->name $sso_user->surname", $session['username']);
      $this->assertGreaterThan($last_login, $session['lastlogin']);
      $this->assertEquals('', $session['userlocale']);
      $this->assertEquals('', $session['usergender']);
      $this->assertEquals(1, $session["userpermissions"]);
    }
}