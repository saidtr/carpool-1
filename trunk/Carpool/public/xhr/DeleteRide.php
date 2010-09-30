<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && !Utils::IsXhrRequest()) {
    die();
}

$action = 'deleted';

$contactId = AuthHandler::getLoggedInUserId();

if (!$contactId) {
    Logger::warn("Delete command sent while no user is logged in");
    die();
}

try {
    
    Service_DeleteUser::run($contactId);
    GlobalMessage::setGlobalMessage(_("Ride deleted. Happy now?"));

    echo json_encode(array('status' => 'ok', 'action' => $action));
} catch (Exception $e) {
    Logger::logException($e);
    echo json_encode(array('status' => 'err', 'action' => $action));
}
