<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

$contact = null;

$contactId = Utils::getParam('c');
$identifier = Utils::getParam('i');

$contact = AuthHandler::authByVerification($contactId, $identifier);
if ($contact) {
    try {
        Service_DeleteUser::run(AuthHandler::getLoggedInUserId());
        GlobalMessage::setGlobalMessage(_('Contact successfully deleted.'), GlobalMessage::INFO);
    } catch (Exception $e) {
        GlobalMessage::setGlobalMessage(_('Deletion failed'). ': ' . _('Internal error.'), GlobalMessage::ERROR);     
    }
} else {
    GlobalMessage::setGlobalMessage(_('Deletion failed'). ': ' . _('Authentication failed.'), GlobalMessage::ERROR);
}

AuthHandler::logout();

Utils::redirect('index.php');

