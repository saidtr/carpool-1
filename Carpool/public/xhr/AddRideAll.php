<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && (!Utils::IsXhrRequest() || !AuthHandler::isSessionExisting())) {
    die();
}
$messages = array();

// Are the contents of this form valid?
$valid = true;

extract($_POST, EXTR_SKIP);

// We need to know the contact name
if (Utils::isEmptyString($name)) {
    $valid = false;
    $messages[] = _("The name is mandatory");
}

// Make sure that locations are set
if ($destCityId == LOCATION_NOT_FOUND && Utils::isEmptyString($destCity)) {
    $valid = false;
    $messages[] = _("Please specify a destination city");    
}

if ($srcCityId == LOCATION_NOT_FOUND && Utils::isEmptyString($srcCity)) {
    $valid = false;
    $messages[] = _("Please specify a source city");    
}

// Email is mandatory
if (empty($email)) {
    $valid = false;
    $messages[] = _("Please specify a valid email address");
}

if (empty($phone)) $phone = null;

$isUpdate = AuthHandler::isLoggedIn();

$action = ($isUpdate) ? 'update' : 'add';

if ($valid) {

    try {
        
        $server = DatabaseHelper::getInstance();
    
        if ($isUpdate) {
            $contactId = AuthHandler::getLoggedInUserId();
            $ride = $server->getRideProvidedByContactId($contactId);   
            $rideId = $ride['Id'];
        } else {
            $contactId = false;
            $rideId = false;
        }
        
        // Add destination and source city in case we don't have them in the DB
        // Assumes we already verified that the names are not empty
        
        if ($destCityId == LOCATION_NOT_FOUND) {
            $destCityId = $server->addCity($destCity);
            if (!$destCity) {
            	throw new Exception("Could not insert city $destCity");
            }
        }
        
        if ($srcCityId == LOCATION_NOT_FOUND && $srcCityId !== $destCityId) {
            $srcCityId = $server->addCity($srcCity);
            if (!$srcCityId) {
            	throw new Exception("Could not insert city $destCity");
            }
        }
        
        if ($isUpdate) {            
            $server->updateContact($contactId, $name, $phone, $email);
        } else {
            // If it is a new ride - register this contact
            $contactId = $server->addContact($name, $phone, $email);
            if (!$contactId) {
            	throw new Exception("Could not insert contact $name");
            }
            // Auto sign in
            AuthHandler::authByContactId($contactId);
        }
        
        // Add or update ride
        if ($isUpdate) {
            if ($server->updateRide($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $wantTo)) {
                GlobalMessage::setGlobalMessage(_("Ride successfully updated."));
            } else {
                throw new Exception("Could not update ride");
            }
        } else {
            $rideId = $server->addRide($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $wantTo);
            if (!$rideId) {
            	throw new Exception("Could not add ride");
            }
            $mailBody = ViewRenderer::renderToString(VIEWS_PATH . '/registrationMail.php', array('contact' => $server->getContactById($contactId))); 
            Utils::sendMail(Utils::buildEmail($email), $name, getConfiguration('mail.addr'), getConfiguration('mail.display'), 'Carpool registration', $mailBody);
            
            if (getConfiguration('notify.immediate') == 1) {
                Service_ShowInterest::run($rideId);
            }
            
        }
        
        echo json_encode(array('status' => 'ok', 'action' => $action));
    } catch (PDOException $e) {
        logException($e);
        echo json_encode(array('status' => 'err', 'action' => $action));
    } catch (Exception $e) {
        logException($e);
        if (ENV == ENV_DEVELOPMENT) {
        	echo json_encode(array('status' => 'err', 'action' => $action, 'msg' => $e->getMessage()));
        } else {
        	echo json_encode(array('status' => 'err', 'action' => $action));
        } 
    }
} else {
    echo json_encode(array('status' => 'invalid', 'action' => $action, 'messages' => $messages));
}