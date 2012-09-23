<?php

include "env.php";
include APP_PATH . "/Bootstrap.php";

$contact = null;
$ref = Utils::getParam('ref');

if (AuthHandler::isLoggedIn()) {
    AuthHandler::logout();
    info('Contact ' . AuthHandler::getLoggedInUserId() . ' logged out');
    GlobalMessage::setGlobalMessage(_('Goodbye!'));
} else {
    warn('User tried to logout without being logged in');
}

if ($ref) {
	// The redirect method is only redirecting to internal pages
    Utils::redirect($ref);
} else {
    Utils::redirect('index.php');
}