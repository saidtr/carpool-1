<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && (!Utils::IsXhrRequest() || !AuthHandler::isSessionExisting())) {
    die();
}

$contactId = AuthHandler::getLoggedInUserId();

if (!$contactId) {
    warn("Toggle activate command sent while no user is logged in");
    die();
}

try {

    $server = DatabaseHelper::getInstance();

    $ride = $server->getRideProvidedByContactId($contactId);
    if (!$ride) {
        throw new Exception("No ride found for contact $contactId");
    }
    $rideId = $ride['Id'];
    if ($ride['Active'] == RIDE_ACTIVE) {
        // Hidden status is always status + 2
        $newStatus = RIDE_INACTIVE;
        $msg = _("Ride de-activated. From now on, this ride will not appear in the search results.");
    } else if ($ride['Active'] == RIDE_INACTIVE) {
        $newStatus = RIDE_ACTIVE;
        $msg = _("Ride activated. You are back in business!");
    } else {
        throw new Exception("Illegal status");
    }
    
    if (!$server->updateRideActive($rideId, $newStatus)) {
        throw new Exception("Could not change status to ride $rideId");
    }
    
    GlobalMessage::setGlobalMessage($msg);
    
    echo json_encode(array('status' => 'ok'));
} catch (PDOException $e) {
    logException($e);
    echo json_encode(array('status' => 'err'));
} catch (Exception $e) {
    logException($e);
    if (ENV == ENV_DEVELOPMENT) {
        echo json_encode(array('status' => 'err', 'msg' => $e->getMessage()));
    } else {
        echo json_encode(array('status' => 'err'));
    }
}
