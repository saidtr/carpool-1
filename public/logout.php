<?php

include "env.php";
include APP_PATH . "/Bootstrap.php";

$contact = null;
$ref = Utils::getParam('ref');

if (isset($_SESSION[SESSION_KEY_AUTH_USER])) {
    AuthHandler::logout();
    info('Contact ' . $_SESSION[SESSION_KEY_AUTH_USER] . ' logged out');
    GlobalMessage::setGlobalMessage(_('Goodbye!'));
} else {
    warn('User tried to logout without being logged in');
}

if ($ref) {
    Utils::redirect($ref);
} else {
    Utils::redirect('index.php');
}