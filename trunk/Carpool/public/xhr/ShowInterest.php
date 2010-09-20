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
if ($destCityId == LOCATION_NOT_FOUND && $srcCityId == LOCATION_NOT_FOUND) {
    $valid = false;
    $messages[] = _("No can do");    
}

// Email is mandatory
if (Utils::isEmptyString($email)) {
    $valid = false;
    $messages[] = _("Email is mandatory here");
}

if ($valid) {
    try {
        
        $server = DatabaseHelper::getInstance();
                
        $contact = $server->getContactByEmail($email);
        
        if ($contact) {
            Logger::debug("$email is already registered");
            $contactId = $contact['Id'];
        } else {            
            // Register this contact
            $contactId = $server->addContact($name, null, $email);
            if (!$contactId) {
            	throw new Exception("Could not insert contact $name");
            }
        }
        
        // Add the ride       
        $rideId = $server->addRide($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $wantTo);
        if (!$rideId) {
        	throw new Exception("Could not add ride");
        }
        //$mailBody = View_RegistrationMail::render($server->getContactById($contactId));
        //Utils::sendMail(Utils::buildEmail($email), $name, getConfiguration('mail.addr'), getConfiguration('mail.display'), 'Carpool registration', $mailBody);
    
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
} else {
    echo json_encode(array('status' => 'invalid', 'action' => $action, 'messages' => $messages));
}