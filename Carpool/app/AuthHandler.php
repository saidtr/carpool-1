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
	
	const SESSION_KEY_AUTH_USER = 'user';
	const SESSION_KEY_AUTH_ROLE = 'role';
	const SESSION_KEY_RUNNING = 'running';
	const SESSION_KEY_GLOBAL_MESSAGE = 'msg';
	const SESSION_KEY_RIDE_REGISTERED = 'ride_registered';
    
    const AUTH_MODE_TOKEN = 1;
    const AUTH_MODE_LDAP  = 2;
    const AUTH_MODE_PASS  = 3;

    public static function init() {
        $lifetime = TWO_WEEKS;
        // Make sure the session will not be marked as garbage
        ini_set("session.gc_maxlifetime", $lifetime);
        session_name('Carpool');
        if (!session_start()) {
            warn("Could not initialize session");
        }
        // Set session to last two weeks (as session_set_cookie_params do not update the time)
        setcookie(session_name(), session_id(), time() + $lifetime, getConfiguration('public.path') . '/');
    }
    
    public static function getRole() {
        if (!isset($_SESSION[self::SESSION_KEY_AUTH_ROLE])) {
            $_SESSION[self::SESSION_KEY_AUTH_ROLE] = ROLE_GUEST;
        }
        return $_SESSION[self::SESSION_KEY_AUTH_ROLE];
    }

    public static function setRole($role) {
        $_SESSION[self::SESSION_KEY_AUTH_ROLE] = $role;
    }

    public static function putUserToken() {
        $_SESSION[self::SESSION_KEY_RUNNING] = '1';
    }
    
    public static function isSessionExisting() {
        return isset($_SESSION) && isset($_SESSION[self::SESSION_KEY_RUNNING]);
    }
    
    public static function isRideRegistered() {
        if (!isset($_SESSION[self::SESSION_KEY_RIDE_REGISTERED])) {
            $contactId = self::getLoggedInUserId();
            if ($contactId && DatabaseHelper::getInstance()->countRidesForContactId($contactId) > 0) {
                $_SESSION[self::SESSION_KEY_RIDE_REGISTERED] = true;    
            } else {
                $_SESSION[self::SESSION_KEY_RIDE_REGISTERED] = false;
            }
        }
        return $_SESSION[self::SESSION_KEY_RIDE_REGISTERED];
    }
    
    public static function updateRegisteredRideStatus($newStatus) {
        debug(__METHOD__ . ': Set ride registered: ' . ($newStatus ? 'true' : 'false'));
        $_SESSION[self::SESSION_KEY_RIDE_REGISTERED] = $newStatus;
    }

    public static function getLoggedInUser() {
        if (isset($_SESSION[self::SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($_SESSION[self::SESSION_KEY_AUTH_USER]);
        }
        return false;
    }

    public static function getLoggedInUserId() {
        if (isset($_SESSION[self::SESSION_KEY_AUTH_USER])) {
            return $_SESSION[self::SESSION_KEY_AUTH_USER];
        }
        return false;
    }
    
    public static function isLoggedIn() {
    	return (isset($_SESSION[self::SESSION_KEY_AUTH_USER]));
    }
    
    public static function authenticate($authHelper, $params) {
        // In case we already have a logged-in user, we'll first log-out
        if (isset($_SESSION[self::SESSION_KEY_AUTH_USER])) {           
            self::logout();
        }       
        
        $res = $authHelper->authenticate($params);
        if ($res !== false) {
            $contactId = $res['Id'];
            $role = $res['Role'];            
            $_SESSION[self::SESSION_KEY_AUTH_USER] = $contactId;
            info('Contact ' . $contactId . ' successfully authenticated');
            self::setRole($role);
            
            return $contactId;            
        } else {
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
        if (isset($_SESSION[self::SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($contactId);
        } else {
            $contact = DatabaseHelper::getInstance()->getContactById($contactId);
            if ($contact) {
                $_SESSION[self::SESSION_KEY_AUTH_USER] = $contactId;
                info('Contact ' . $contactId . ' automatically authenticated');
                return $contact;
            } else {
                warn('Contact "' . $contactId . '" was not found in the database');
                return false;
            }
        }
    }
    
    public static function getAuthMode() {
        $mode = getConfiguration('auth.mode');
        if ($mode === false) {
            throw new Exception('Mandatory configuration value auth.mode is not defined in the configuration file.');
        }
        return $mode;
    }

    public static function logout() {
        debug(__METHOD__);

        $params = session_get_cookie_params();
        // Destroy the cookie associated with that session
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

        // Finally, destroy the session.
        session_destroy();
    }

    /**
     * Factory method for authentication helpers 
     * 
     * @param int $mode Authentication mode to use
     * @return IAuthenticationHelper implementation 
     */
    public static function getAuthenticationHelper($mode = null) {
        if (!isset($mode)) {
            $mode = (int) getConfiguration('auth.mode', 0);
        }
        $authHelper = null;

        debug(__METHOD__ . ": Mode is $mode");

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
        
        return $authHelper;
    }

}