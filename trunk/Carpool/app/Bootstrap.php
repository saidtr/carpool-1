<?php

// Constants that do not need to be customized
define('STATUS_DONT_CARE', 0);
define('STATUS_LOOKING', 1);
define('STATUS_OFFERED', 2);
define('STATUS_OFFERED_HIDE', 1);

define('TIME_IRRELEVANT', 0);
define('TIME_DIFFERS', -1);

define('LOCATION_NOT_FOUND', -1);
define('LOCATION_DONT_CARE', -2);

define('SESSION_KEY_AUTH_USER', 'user');
define('SESSION_KEY_RUNNING', 'running');
define('SESSION_KEY_GLOBAL_MESSAGE', 'msg');

// Error reporting
if (ENV === ENV_DEVELOPMENT) {
	error_reporting(0); 
} else {
	error_reporting(E_ALL | E_STRICT); 
}

// Simple auto loading:
//   View classes (name starts with View) - look in app/views
//   Standard classes - look in app/
function __autoload($className) {
	if (strncmp($className, 'View', 4) === 0) {
		require_once APP_PATH . '/views/' . $className . '.php';
	} else {
    	require_once APP_PATH . '/' . $className . '.php';
	}
}

// Global configuration
$globalConf = parse_ini_file(CONF_PATH . '/carpool.ini', false);
if (!$globalConf) {
	// We can't really do anything without that
	Logger::err('Init: Could not parse configuration file: ' . CONF_PATH . '/carpool.ini');
	die('Could not parse configuration file. Aborting.');
}

$GLOBALS['conf'] = $globalConf;

function getConfiguration($key) {
	$globalConf = $GLOBALS['conf'];
	if (!isset($globalConf) || !isset($globalConf[$key])) {
		Logger::err(__METHOD__ . ": Configuration not set or configuration is not available for $key");
		return false;
	}
	return $globalConf[$key];
}

// Locale
$localeManager = LocaleManager::getInstance();
$localeManager->init();

// Start session
AuthHandler::init();

Logger::info('Bootstrap done.');
