<?php

/**
 * The bootstrap class handles all basic initializations
 * and auto-loading of classes.
 * 
 * This class should usually included in the beginning of
 * each public script or a job 
 * 
 * @author itay
 * 
 */

// Constants that do not need to be customized
define('STATUS_DONT_CARE', 0);
define('STATUS_LOOKING', 1);
define('STATUS_OFFERED', 2);
define('STATUS_SHARING', STATUS_LOOKING | STATUS_OFFERED);

define('RIDE_INACTIVE', 0);
define('RIDE_ACTIVE', 1);

define('TIME_IRRELEVANT', 0);
define('TIME_DIFFERS', -1);

define('LOCATION_NOT_FOUND', -1);
define('LOCATION_DONT_CARE', -2);

define('SESSION_KEY_AUTH_USER', 'user');
define('SESSION_KEY_AUTH_ROLE', 'role');
define('SESSION_KEY_RUNNING', 'running');
define('SESSION_KEY_GLOBAL_MESSAGE', 'msg');

// Two weeks (in seconds) - 60 * 60 * 24 * 14
define('TWO_WEEKS', 1209600);

// Random visitor
define('ROLE_GUEST', 1);
// Authorized but not identified. Useful when there's an organization-wide password,
// IP based access, etc.
define('ROLE_AUTHORIZED_ACCESS', 2);
// Identified (e.g. by AD credentials), but not registered a ride.
define('ROLE_IDENTIFIED', 3);
// Identified and registered.
define('ROLE_IDENTIFIED_REGISTERED', 4);
// Administrator, may access resources such as CMS.
define('ROLE_ADMINISTRATOR', 5);

// Error reporting and assertions
if (ENV === ENV_DEVELOPMENT) {
	error_reporting(E_ALL | E_STRICT); 
	
	// Activate assertions
    assert_options(ASSERT_ACTIVE, true);
    assert_options(ASSERT_WARNING, false);
    assert_options(ASSERT_BAIL, true);
    assert_options(ASSERT_QUIET_EVAL, false);
    
    // Assert handler - log the failure
    function loggerAssertHandler($file, $line, $code) {
        err("Assertion Failed in $file, line $line: $code");
        echo("<p>Assertion Failed in $file, line $line: $code</p>");
    }
    
    // Set up the callback
    assert_options(ASSERT_CALLBACK, 'loggerAssertHandler');
    
} else {
	error_reporting(0);
	assert_options(ASSERT_ACTIVE, 0); 
}

// Handle uncaught exceptions:  
//   Show error page with a relevant message
function uncaughtExceptionHandler($e) {
    logException($e);
    ob_end_clean(); 
    header('HTTP/1.1 500 Internal Server Error');
    ViewRenderer::render('views/errorPage.php', array('exception' => $e));
}

set_exception_handler('uncaughtExceptionHandler');

// Simple auto loading:
//   View classes (name starts with View) - look in app/views
//   Standard classes - look in app/
function __autoload($className) {
	if (strncmp($className, 'View_', 5) === 0) {
		require_once APP_PATH . '/views/' . $className . '.php';
	} elseif (strncmp($className, 'Service_', 8) === 0) {
		require_once APP_PATH . '/services/' . $className . '.php';
	} else {
    	require_once APP_PATH . '/' . $className . '.php';
	}
}

// Global configuration
$globalConf = parse_ini_file(CONF_PATH . '/' . CONF_FILE, false);
if (!$globalConf) {
	// We can't really do anything without that
	throw Exception('Could not parse configuration file ' . (CONF_PATH . '/' . CONF_FILE));
}

$GLOBALS['conf'] = $globalConf;

function getConfiguration($key, $default = false) {
    global $globalConf;
    assert('isset($globalConf)');
    
    if (!isset($globalConf[$key])) {
        return $default;
    }
    return $globalConf[$key];
}

// Logger
$logger = null;
$logLevel = getConfiguration('log.level');

try {
    if ($logLevel > Logger::LOG_NONE) {
        $logger = new Logger(str_replace("~", BASE_PATH, getConfiguration('log.file')), $logLevel);
    }
} catch (Exception $e) {
    // Ignore
}

if ($logger == null) {
    $logger = new NullLogger();
}


$GLOBALS['logger'] = $logger;

function debug($str) {
    global $logger;
    $logger->doLog(Logger::LOG_DEBUG, $str);
}
 
function info($str) {
    global $logger;
    $logger->doLog(Logger::LOG_INFO, $str);
}
 
function warn($str) {
    global $logger;
    $logger->doLog(Logger::LOG_WARN, $str);
}
 
function err($str) {
    global $logger;
    $logger->doLog(Logger::LOG_ERR, $str);
}

function logException(Exception $e) {
    global $logger;
    $logger->logException($e);
}
 
// Locale
$localeManager = LocaleManager::getInstance();
$localeManager->init();

// Start session
AuthHandler::init();

// Initialize the ACL
$acl = new SimpleAcl();

$acl->addRole(ROLE_GUEST);
$acl->addRole(ROLE_AUTHORIZED_ACCESS, ROLE_GUEST);
$acl->addRole(ROLE_IDENTIFIED, ROLE_GUEST);
$acl->addRole(ROLE_IDENTIFIED_REGISTERED, ROLE_IDENTIFIED);
$acl->addRole(ROLE_ADMINISTRATOR, ROLE_IDENTIFIED_REGISTERED);

if (ENV === ENV_DEVELOPMENT) {
    $acl->addResource(ROLE_GUEST, 'webres.php');    
}

$acl->addResource(ROLE_GUEST, array('auth.php', 'optout.php'));
if (getConfiguration('auth.mode') == AuthHandler::AUTH_MODE_PASS) {
    $acl->addResource(ROLE_GUEST, array('join.php', 'help.php', 'AddRideAll.php'));
} else if (AuthHandler::getAuthMode() == AuthHandler::AUTH_MODE_TOKEN) {
    $acl->addResource(ROLE_GUEST, array('join.php', 'help.php', 'index.php', 'AddRideAll.php', 'feedback.php', 'SearchRides.php'));
}
$acl->addResource(ROLE_IDENTIFIED, array('join.php', 'help.php', 'index.php', 'feedback.php', 'logout.php', 'thanks.php', 'SearchRides.php', 'AddRideAll.php'));
$acl->addResource(ROLE_IDENTIFIED_REGISTERED, array('ActivateToggle.php', 'DeleteRide.php', 'ShowInterest.php'));
// Content management
$acl->addResource(ROLE_ADMINISTRATOR, array('translations.php'));

// Enfore access control
$role = AuthHandler::getRole();
$resource = Utils::getRunningScript();

if (!$acl->isAllowed($role, $resource)) {    
    if (($role == ROLE_GUEST) && $acl->isAllowed($role, 'auth.php')) {
        // Not allowed: if not logged in and allowed to - redirect to login page
        GlobalMessage::setGlobalMessage(_('Please login to access this page'), GlobalMessage::ERROR);
        Utils::redirect('auth.php', array('ref' =>  $resource));
    } else if ($acl->isAllowed($role, 'auth.php')) {
        // User is logged in but not permitted to use this page
        header("HTTP/1.1 401 Unauthorized");
        die ('<p>' . _('Access Denied') . '</p>');
    } else {
        // User is not logged-in and not allowed to do that - totally forbidden
        header("HTTP/1.1 403 Forbidden");
        die ('<p>' . _('Sorry, you are not allowed to use this application.') . '</p>');
    }
}

$GLOBALS['acl'] = $acl;

info('Bootstrap done.');
