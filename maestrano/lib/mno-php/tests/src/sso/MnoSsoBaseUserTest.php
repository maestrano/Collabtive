<?php

class MnoSsoBaseUserTest extends PHPUnit_Framework_TestCase
{
    private $_saml_settings;

    public function setUp()
    {
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
    
    public function testUserContruction()
    {
        $assertion = file_get_contents(TEST_ROOT . '/support/sso-responses/response_ext_user.xml.base64');
        $response = new OneLogin_Saml_Response($this->_saml_settings, $assertion);
        $response_attr = $response->getAttributes();
        $sso_user = new MnoSsoBaseUser($response);
        echo $response_attr['mno_uid'][0];
        
        $this->assertEquals($sso_user->uid, $response_attr['mno_uid'][0]);
        $this->assertEquals($sso_user->sso_session, $response_attr['mno_session'][0]);
        $this->assertEquals($sso_user->sso_session_recheck, new DateTime($response_attr['mno_session_recheck'][0]));
        $this->assertEquals($sso_user->email, $response_attr['email'][0]);
        $this->assertEquals($sso_user->name, $response_attr['name'][0]);
        $this->assertEquals($sso_user->surname, $response_attr['surname'][0]);
        $this->assertEquals($sso_user->app_owner, $response_attr['app_owner'][0]);
        $this->assertEquals($sso_user->organizations, json_decode($response_attr['organizations'][0],true));
    }
}