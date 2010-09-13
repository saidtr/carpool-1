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
    
    public static function init() {
        if (!session_start()) {
            Logger::warn("Could not initialize session");
        }
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

    public static function authByVerification($contactId, $identifier) {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($contactId);
        } else {
            $contact = DatabaseHelper::getInstance()->getContactByIdentifier($contactId, $identifier);
            if ($contact) {
                $_SESSION[SESSION_KEY_AUTH_USER] = $contact['Id'];
                Logger::info('Contact ' . $contact['Id'] . ' successfully authenticated');
                return $contact;
            } else {
                Logger::warn('Authentication failed for contact "' . $email . '" and token "' . $identifier . '"');
                return false;
            }
        }
    }
    
    public static function authByContactId($contactId) {
        if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
            return DatabaseHelper::getInstance()->getContactById($contactId);
        } else {
            $contact = DatabaseHelper::getInstance()->getContactById($contactId);
            if ($contact) {
                $_SESSION[SESSION_KEY_AUTH_USER] = $contactId;
                Logger::info('Contact ' . $contactId . ' automatically authenticated');
                return $contact;
            } else {
                Logger::warn('Contact "' . $contactId . '" was not found in the database');
                return false;
            }
        }
    }
    
    public static function logout() {
        Logger::debug(__METHOD__);
        unset($_SESSION[SESSION_KEY_AUTH_USER]);
    }

}