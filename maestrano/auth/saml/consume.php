<?php
/**
 * SAMPLE Code to demonstrate how to handle a SAML assertion response.
 *
 * The URL of this file will have been given during the SAML authorization.
 * After a successful authorization, the browser will be directed to this
 * link where it will send a certified response via $_POST.
 */

error_reporting(E_ALL);

$mno_settings = NULL;
require '../../app/init/auth.php';

session_start();

// Check where we should redirect the user
// after successful login
if (isset($_SESSION['previous_url'])) {
  $after_signin_url = $_SESSION['previous_url'];
} else {
  $after_signin_url = "http://$_SERVER[HTTP_HOST]/";
}
error_log($_POST['SAMLResponse']);
$samlResponse = new OneLogin_Saml_Response($mno_settings->getSamlSettings(), $_POST['SAMLResponse']);

try {
    if ($samlResponse->isValid()) {
        
        // Get Maestrano User
        $sso_user = new MnoSsoUser($samlResponse, $conn);
        
        // Try to match the user with a local one
        $sso_user->matchLocal();
        
        // If user was not matched then attempt
        // to create a new local user
        if (is_null($sso_user->local_id)) {
          $sso_user->createLocalUser();
          //echo 'Attempting to create new user <br/>';
        }
        
        // If user is matched then sign it in
        // Refuse access otherwise
        if ($sso_user->local_id) {
          //echo 'Access Granted <br/>';
          $sso_user->signIn();
          //echo 'Signed In <br/>';
          header("Location: $after_signin_url");
        } else {
          //echo 'Access Refused <br/>';
          header("Location: $mno_settings->sso_access_unauthorized_url");
        }
        echo '<br/><br/>';
        
        echo 'You are: ' . $samlResponse->getNameId() . '<br>';
        echo 'After Signin Url: ' . $after_signin_url . '<br>';
        $attributes = $samlResponse->getAttributes();
        if (!empty($attributes)) {
            echo 'You have the following attributes:<br>';
            echo '<table><thead><th>Name</th><th>Values</th></thead><tbody>';
            foreach ($attributes as $attributeName => $attributeValues) {
                echo '<tr><td>' . htmlentities($attributeName) . '</td><td><ul>';
                    foreach ($attributeValues as $attributeValue) {
                        echo '<li>' . htmlentities($attributeValue) . '</li>';
                    }
                echo '</ul></td></tr>';
            }
            echo '</tbody></table>';
        }
        echo var_dump($_SESSION);
    }
    else {
        echo 'Invalid SAML response.';
    }
}
catch (Exception $e) {
    echo 'Invalid SAML response: ' . $e->getMessage();
}
