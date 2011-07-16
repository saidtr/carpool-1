<?php

/**
 * 
 * This class handles the global message shown to the user.
 * Only one message is supported each time.
 * 
 * @author itay
 *
 */
class GlobalMessage {
	
	const INFO = 1;
	const ERROR = 2;
    
	/**
	 * 
	 * Set the global message and it's type. Only one message
	 * is supported at a time; new messages would override the
	 * existing message. Message is set until call to the "clear"
	 * method.
	 * @param string $msg
	 * @param int $type
	 * @return True iff the message successfully set.
	 */
    public static function setGlobalMessage($msg, $type = self::INFO) {
        if (AuthHandler::isSessionExisting()) {
            $_SESSION[SESSION_KEY_GLOBAL_MESSAGE] = array('msg' => $msg, 'type' => $type);
            return true;
        }
        return false;
    }
    
    /**
     * 
     * @return string The global message, or false if no such exists.
     */
    public static function getGlobalMessage() {
        if (AuthHandler::isSessionExisting() && isset($_SESSION[SESSION_KEY_GLOBAL_MESSAGE])) {
            return $_SESSION[SESSION_KEY_GLOBAL_MESSAGE];
        }
        return false;
    }
    
    /**
     * 
     * Deletes the global message
     */
    public static function clear() {
        unset ($_SESSION[SESSION_KEY_GLOBAL_MESSAGE]);
    }
    
    
}