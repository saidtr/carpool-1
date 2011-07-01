<?php

class AuthenticationHelperLdap implements IAuthenticationHelperInteractive {
    
    const LDAP_DEFAULT_PORT = 389;
    
    // LDAP relevant error codes
    // For full list of LDAP errors, see first comment in http://il2.php.net/manual/en/function.ldap-errno.php
    const LDAP_INAPPROPRIATE_AUTH  = 48;
    const LDAP_INVALID_CREDENTIALS = 49;

    /**
     * Taken from Zend Framework
     * 
     * Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
     *
     * Any control characters with an ACII code < 32 as well as the characters with special meaning in
     * LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
     * backslash followed by two hex digits representing the hexadecimal value of the character.
     * @see Net_LDAP2_Util::escape_filter_value() from Benedikt Hallinger <beni@php.net>
     * @link http://pear.php.net/package/Net_LDAP2
     * @author Benedikt Hallinger <beni@php.net>
     *
     * @param  string $val Value to escape
     * @return string Escaped value
     */
    private function ldap_escape($val) {
        $val = str_replace(array('\\', '*', '(', ')'), array('\5c', '\2a', '\28', '\29'), $val);
        for ($i = 0; $i < strlen($val); $i++) {
            $char = substr($val, $i, 1);
            if (ord($char)<32) {
                $hex = dechex(ord($char));
                if (strlen($hex) == 1) $hex = '0' . $hex;
                $val = str_replace($char, '\\' . $hex, $val);
            }
        }
        return $val;
    }
    
    function authenticate($params) {       
        assert('isset($params["user"]) && isset($params["password"])');
    
        $con = false;
        if (($domain = getConfiguration('auth.ldap.domain')) !== false) {
            $port = (int) getConfiguration('auth.ldap.port', self::LDAP_DEFAULT_PORT);
            $con = ldap_connect($domain, $port);
        }
        if ($con === false) {
            throw new Exception(__METHOD__ . ": Failed to connect to $domain in port $port");
        }
        
        $authUser = $user = $this->ldap_escape($params['user']);
        $pass = $this->ldap_escape($params['password']);
        
        $ldapDomainName = getConfiguration('auth.ldap.domain.name');
        if ($ldapDomainName) {
            $authUser = $ldapDomainName . '\\' . $authUser;
        }
        
        debug(__METHOD__ . ": Trying to authenticate $authUser against $domain");
        
        if (ldap_bind($con, $authUser, $pass)) {
            // We're assuming that the email used is as the user name
            $email = $email = Utils::buildEmail($user);
            
            // Fetch contact
            $contact = DatabaseHelper::getInstance()->getContactByEmail($email);
            if ($contact !== false) {
                return array('Id' => $contact['Id'], 'Role' => $contact['Role']);    
            } else {
                // Contact is not in the database - we better create it
                // TODO: Put the option to read data
                return DatabaseHelper::getInstance()->addContact('', '', $email, ROLE_IDENTIFIED);
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
    
    function putLogonFormFields() { 
         ViewRenderer::render('views/authFormLdap.php');
    }
    
    function validateForm($request) {
        return (
            isset($request['user']) && 
            !Utils::isEmptyString($request['user']) &&
            isset($request['password']) &&
            !Utils::isEmptyString($request['password'])
            );
    }
    
}