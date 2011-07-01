<?php

class AuthenticationHelperPassword implements IAuthenticationHelperInteractive {
    
    function authenticate($params) {
        assert('isset($params["email"]) && isset($params["password"])');
        
        // TODO: A primitive brute-force defense?
        
        // We must call buildEmail as we may have explicitely added the 
        // domain suffix during registration
        $email = Utils::buildEmail($params['email']);      
        $pass = $params['password'];
        
        // Created a hashed hexadecimal string, use the salt if possible
        $hashed = Utils::hashPassword($pass);
        $contact = DatabaseHelper::getInstance()->getContactByEmail($email);
        if ($contact !== false) {
            if ($contact['Identifier'] === $hashed) {
                info(__METHOD__ . ': Contact ' . $contact['Id'] . ' succesfully authenticated');
                return array('Id' => $contact['Id'], 'Role' => $contact['Role']);
            } else {
                warn(__METHOD__ . ': Contact ' . $contact['Id'] . ' failed to authorize: wrong password');
            }
            
        }
        return false;
    }
    
    function putLogonFormFields() { 
         ViewRenderer::render('views/authFormPassword.php');
    }
    
    function validateForm($request) {
        return (
            isset($request['email']) && 
            !Utils::isEmptyString($request['email']) &&
            isset($request['password']) &&
            !Utils::isEmptyString($request['password'])
            );
    }
    
    
}