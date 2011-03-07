<?php

define('ENV_DEVELOPMENT', 'dev');
define('ENV_PRODUCTION', 'prod');

define('ENV', ENV_DEVELOPMENT);

// Basic path
define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

// Path
define('APP_PATH', BASE_PATH . '/app');
define('CONF_PATH', BASE_PATH . '/conf');
define('DATA_PATH', BASE_PATH . '/data');
define('LOCALE_PATH', BASE_PATH . '/lang');
define('VIEWS_PATH', APP_PATH . '/views');

// Configuration file
defined('CONF_FILE') or define('CONF_FILE', 'carpool.ini');

ini_set('date.timezone', 'Asia/Jerusalem');