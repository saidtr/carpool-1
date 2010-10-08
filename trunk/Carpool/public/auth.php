<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

$contact = null;

$action = Utils::getParam('action', 'login');

if ($action === 'login') {
	$contactId = Utils::getParam('c');
	$identifier = Utils::getParam('i');
	
	$contact = AuthHandler::authByVerification($contactId, $identifier);
	if (!$contact) {
	    GlobalMessage::setGlobalMessage(_('Authentication failed.'), GlobalMessage::ERROR);
	}
} else if ($action === 'logout') {
	if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
		info('Contact ' . $_SESSION[SESSION_KEY_AUTH_USER] . ' logged out');
		GlobalMessage::setGlobalMessage(_('Goodbye!'));
	} else {
		warn('User tried to logout without being logged in');
	}
	AuthHandler::logout();
}

if ($contact) {
	Utils::redirect('join.php');	
} else {
	Utils::redirect('index.php');
}


