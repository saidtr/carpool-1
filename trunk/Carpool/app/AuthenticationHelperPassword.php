<?php

class AuthenticationHelperPassword implements IAuthenticationHelper {
    
    function init() {
        return true;
    }
    
    function authenticate($params) {
        assert('isset($params["user"]) && isset($params["password"])');
        
        // TODO: A primitive brute-force defense?
        
        // We must call buildEmail as we may have explicitely added the 
        // domain suffix during registration
        $email = Utils::buildEmail($params['user']);      
        $pass = $params['password'];
        
        // Created a hashed hexadecimal string, use the salt if possible
        $hashed = Utils::hashPassword($pass);
        $contact = DatabaseHelper::getInstance()->getContactByEmail($email);
        if ($contact !== false) {
            if ($contact['Identifier'] === $hashed) {
                info(__METHOD__ . ': Contact ' . $contact['Id'] . ' succesfully authenticated');
                return $contact['Id'];
            } else {
                warn(__METHOD__ . ': Contact ' . $contact['Id'] . ' failed to authorize: wrong password');
            }
            
        }
        return false;
    }
    
}