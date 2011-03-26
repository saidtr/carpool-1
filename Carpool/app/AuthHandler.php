<?php

/**
 * 
 * A wrapper class for various session and authorization
 * functions. This is a static, stateless class.
 * 
 * @author itay
 *
 */
class AuthHandler {
    
    const AUTH_MODE_TOKEN = 0;
    const AUTH_MODE_LDAP  = 1;
    const AUTH_MODE_PASS  = 2;
    
    public static function init() {
        if (!session_start()) {
            warn("Could not initialize session");
        }
    }
    
    public static function getRole() {
        if (!isset($_SESSION[SESSION_KEY_AUTH_ROLE])) {
            $_SESSION[SESSION_KEY_AUTH_ROLE] = ROLE_GUEST;
        }
        return $_SESSION[SESSION_KEY_AUTH_ROLE];
    }

    public static function setRole($role) {
        $_SESSION[SESSION_KEY_AUTH_ROLE] = $role;
    }

    public static function putUserToken() {
        $_SESSION[SESSION_KEY_RUNNING] = '1';
    }
    
    public static function isSessionExisting() {
        return isset($_SESSION) && isset($_SESSION[SESSION_KEY_RUNNING]);
    }

    public static function isLoggedIn() {
        return isset($_SESSION[SESSION_KEY_AUTH_USER]);
    }

    public static function getLoggedInUser() {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($_SESSION[SESSION_KEY_AUTH_USER]);
        }
        return false;
    }

    public static function getLoggedInUserId() {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            return $_SESSION[SESSION_KEY_AUTH_USER];
        }
        return false;
    }
    
    public static function authenticate($params, $mode = null) {
        if (!isset($mode)) {
            $mode = (int) getConfiguration('auth.mode', 0);
        }
        $authHelper = null;
        switch ($mode) {
            case self::AUTH_MODE_TOKEN : 
                $authHelper = new AuthenticationHelperToken(); 
                break;
            case self::AUTH_MODE_LDAP  : 
                $authHelper = new AuthenticationHelperLdap(); 
                break;
            case self::AUTH_MODE_PASS  : 
                $authHelper = new AuthenticationHelperPassword(); 
                break;
            default:
                err(__METHOD__ . ": Illegal authentication mode: $mode");
                return false;
        }

        // In case we already have a logged-in user, we'll first log-out
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {           
            self::logout();
        }       
        
        $contactId = $authHelper->authenticate($params);
        if ($contactId !== false) {
            $_SESSION[SESSION_KEY_AUTH_USER] = $contactId;
            info('Contact ' . $contactId . ' successfully authenticated');
            return $contactId;            
        } else {
            return false;
        }
    }

    public static function authByVerification($contactId, $identifier) {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            // In case we already have a logged-in user, we'll first log-out
            self::logout();
        }
        
        $contact = DatabaseHelper::getInstance()->getContactByIdentifier($contactId, $identifier);
        if ($contact) {
            $_SESSION[SESSION_KEY_AUTH_USER] = $contact['Id'];
            info('Contact ' . $contact['Id'] . ' successfully authenticated');
            return $contact;
        } else {
            warn('Authentication failed for contact "' . $email . '" and token "' . $identifier . '"');
            return false;
        }
    }

    /**
     * Automatically authenticate a given contact. This function should
     * only be used when there is no need to authenticate, such as right
     * after signing up
     * 
     * @param int $contactId Contact id of contact
     * @returns Contact data if authenticated, or false if no such contact exists
     */
    public static function authByContactId($contactId) {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($contactId);
        } else {
            $contact = DatabaseHelper::getInstance()->getContactById($contactId);
            if ($contact) {
                $_SESSION[SESSION_KEY_AUTH_USER] = $contactId;
                info('Contact ' . $contactId . ' automatically authenticated');
                return $contact;
            } else {
                warn('Contact "' . $contactId . '" was not found in the database');
                return false;
            }
        }
    }
    
    public static function logout() {
        debug(__METHOD__);
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            unset($_SESSION[SESSION_KEY_AUTH_USER]);
        }
    }

}