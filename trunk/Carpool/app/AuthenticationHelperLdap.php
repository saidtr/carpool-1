<?php

class AuthenticationHelperLdap implements IAuthenticationHelper {
    
    const LDAP_DEFAULT_PORT = 389;
    
    // LDAP relevant error codes
    // For full list of LDAP errors, see first comment in http://il2.php.net/manual/en/function.ldap-errno.php
    const LDAP_INAPPROPRIATE_AUTH  = 48;
    const LDAP_INVALID_CREDENTIALS = 49;
    
    function authenticate($params) {       
        assert('isset($params["user"]) && isset($params["password"])');
        
        $con = false;
        if (($domain = getConfiguration('auth.ldap.domain', 'Unspecified domain')) !== false) {
            $con = ldap_connect($domain, getConfiguration('auth.ldap.port', self::LDAP_DEFAULT_PORT));
        }
        if ($con === false) {
            throw new Exception(__METHOD__ . ": Failed to connect to $domain");
        }
        
        $user = $params['user'];
        $pass = $params['password'];
        
        if (ldap_bind($con, $user, $pass)) {
            // We're assuming that the email used is as the user name
            $email = $email = Utils::buildEmail($user);
            
            // Fetch contact
            $contact = DatabaseHelper::getInstance()->getContactByEmail($email);
            if ($contact !== false) {
                return $contact['Id'];    
            } else {
                // Contact is not in the database - we better create it
                return DatabaseHelper::getInstance()->addContact('', '', $email);
            }           
        } else {
            $errCode = ldap_errno($con);
            if ($errCode == self::LDAP_INAPPROPRIATE_AUTH || $errCode == self::LDAP_INVALID_CREDENTIALS) {
                // Invalid credentials - simply fail
                return false;
            }
            
            // Internal error
            throw new Exception(__METHOD__ . " : LDAP error: " . ldap_err2str($errCode));
        }
        
        ldap_unbind($con);
        
        return true;
    }
    
}