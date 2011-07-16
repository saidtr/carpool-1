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
            debug("$email is already registered");
            $contactId = $contact['Id'];
        } else {            
            // Register this contact
            $contactId = $server->addContact(null, null, $email);
            if (!$contactId) {
            	throw new Exception("Could not insert contact $name");
            }
        }
        
        // Add the ride       
        $rideId = $server->addRide($srcCityId, null, $destCityId, null, null, null, $contactId, null, $wantTo);
        if (!$rideId) {
        	throw new Exception("Could not add ride");
        }
        
        //$mailBody = View_RegistrationMail::render($server->getContactById($contactId));
        //Utils::sendMail(Utils::buildEmail($email), $name, getConfiguration('mail.addr'), getConfiguration('mail.display'), 'Carpool registration', $mailBody);
    
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
} else {
    echo json_encode(array('status' => 'invalid', 'messages' => $messages));
}