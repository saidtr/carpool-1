<?php

define('ENV_DEVELOPMENT', 'dev');
define('ENV_PRODUCTION', 'prod');

define('ENV', ENV_DEVELOPMENT);

// Basic path
define('BASE_PATH', realpath(dirname(__FILE__) . '/..'));

// Public path, as env.php is always there
// TODO: Just need a function to convert absolute path to URI
//define('PUBLIC_BASE_PATH', dirname(__FILE__));
//echo PUBLIC_BASE_PATH . '<br>' . __FILE__ . '<br>' . $_SERVER['PATH_INFO'];

// Path
define('APP_PATH', BASE_PATH . '/app');
define('CONF_PATH', BASE_PATH . '/conf');
define('DATA_PATH', BASE_PATH . '/data');
define('LOCALE_PATH', BASE_PATH . '/lang');

ini_set('date.timezone', 'Asia/Jerusalem');