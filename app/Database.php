<?php

// Database wrapper
// Not in use for now. Might be useful for database portability in the future
class Database {
	
	private static $_db = array();

	public static function getConnection($database) {
		if (!isset(self::$_db[$database])) {
	        self::$_db[$database] = new PDO('sqlite:' . DATA_PATH . '/' . $database . '.db');
	    }
	    return self::$_db[$database];
	}


}
