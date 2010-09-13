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

    $server = DatabaseHelper::getInstance();

    $ride = $server->getRideByContactId($contactId);
    if (!$ride) {
        throw new Exception("No ride found for contact $contactId");
    }
    $rideId = $ride['Id'];

    if (!$server->deleteContact($contactId)) {
        throw new Exception("Could not delete contact $contactId");
    }
    
    if (!$server->deleteRide($rideId)) {
        throw new Exception("Could not delete ride $rideId");
    }
    
    AuthHandler::logout();
    
    GlobalMessage::setGlobalMessage(_("Ride deleted. Happy now?"));

    echo json_encode(array('status' => 'ok', 'action' => $action));
} catch (PDOException $e) {
    Logger::logException($e);
    echo json_encode(array('status' => 'err', 'action' => $action));
} catch (Exception $e) {
    Logger::logException($e);
    if (ENV == ENV_DEVELOPMENT) {
        echo json_encode(array('status' => 'err', 'action' => $action, 'msg' => $e->getMessage()));
    } else {
        echo json_encode(array('status' => 'err', 'action' => $action));
    }
}
