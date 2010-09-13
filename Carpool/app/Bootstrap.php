<?php

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

define('DEFAULT_DOMAIN', '');
define('DOMAIN', '');

define('DEFAULT_LOCALE', 'en');

// Error reporting
if (ENV === ENV_DEVELOPMENT) {
	error_reporting(0); 
} else {
	error_reporting(E_ALL | E_STRICT); 
}

function __autoload($className) {
	if (strncmp($className, 'View', 4) === 0) {
		require_once BASE_PATH . '/app/views/' . $className . '.php';
	} else {
    	require_once BASE_PATH . '/app/' . $className . '.php';
	}
}

$localeManager = LocaleManager::getInstance();
$localeManager->init();

AuthHandler::init();

Logger::info('Bootstrap done.');
