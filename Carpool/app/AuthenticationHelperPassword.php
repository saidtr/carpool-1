<?php

class AuthenticationHelperPassword implements IAuthenticationHelper {
    
    function init() {
        return true;
    }
    
    function authenticate($params) {
        assert('isset($params["email"]) && isset($params["password"])');
        
        // TODO: A primitive brute-force defense?
        
        $email = $params['email'];
        $pass = $params['password'];
        
        // Created a hashed hexadecimal string, use the salt if possible
        $hashed = Utils::hashPassword($pass);
        $contact = DatabaseHelper::getInstance()->getContactByEmail($email);
        if ($contact !== false) {
            if ($contact['Identifier'] === $hashed) {
                info(__METHOD__ . ': Contact ' . $contact['Id'] . ' succesfully authenticated');
                return true;
            } else {
                warn(__METHOD__ . ': Contact ' . $contact['Id'] . ' failed to authorize: wrong password');
            }
            
        }
        return false;
    }
    
}