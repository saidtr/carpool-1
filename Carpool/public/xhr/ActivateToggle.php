<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && (!Utils::IsXhrRequest() || !AuthHandler::isSessionExisting())) {
    die();
}

$contactId = AuthHandler::getLoggedInUserId();

if (!$contactId) {
    Logger::warn("Toggle activate command sent while no user is logged in");
    die();
}

try {

    $server = DatabaseHelper::getInstance();

    $ride = $server->getRideProvidedByContactId($contactId);
    if (!$ride) {
        throw new Exception("No ride found for contact $contactId");
    }
    $rideId = $ride['Id'];
    if ($ride['Status'] == STATUS_OFFERED) {
        $newStatus = STATUS_OFFERED_HIDE;
        $msg = _("Ride de-activated. From now on, this ride will not appear in the search results.");
    } else if ($ride['Status'] == STATUS_OFFERED_HIDE) {
        $newStatus = STATUS_OFFERED;
        $msg = _("Ride activated. You are back in business!");
    } else {
        throw Exception("Illegal status");
    }
    
    if (!$server->updateRideStatus($rideId, $newStatus)) {
        throw new Exception("Could not change status to ride $rideId");
    }
    
    GlobalMessage::setGlobalMessage($msg);
    
    echo json_encode(array('status' => 'ok'));
} catch (PDOException $e) {
    Logger::logException($e);
    echo json_encode(array('status' => 'err'));
} catch (Exception $e) {
    Logger::logException($e);
    if (ENV == ENV_DEVELOPMENT) {
        echo json_encode(array('status' => 'err', 'msg' => $e->getMessage()));
    } else {
        echo json_encode(array('status' => 'err'));
    }
}
