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
if (getConfiguration('mode.single.dest')) {
    $destCityId = getConfiguration('default.dest.city', 0);
    $destLocation = getConfiguration('default.dest.loc', ''); 
} else if ($destCityId == LOCATION_NOT_FOUND && Utils::isEmptyString($destCity)) {
    $valid = false;
    $messages[] = _("Please specify a destination city");    
}

if ($srcCityId == LOCATION_NOT_FOUND && Utils::isEmptyString($srcCity)) {
    $valid = false;
    $messages[] = _("Please specify a source city");    
}

// Email is mandatory
if (!empty($email)) {
    // In domain-users mode, we simply prohibit mail addresses with '@' in them
    if ((getConfiguration('mode.domain.users', 0) == 1) && strpos($email, '@') !== false) {
        $valid = false;
        $messages[] = _("Please specify a valid email address, without the domain suffix");
    }
    
    // Make sure that the email has a domain part
    $email = Utils::buildEmail($email);
}

if (empty($email) || (filter_var($email, FILTER_VALIDATE_EMAIL) === false)) {
    $valid = false;
    $messages[] = _("Please specify a valid email address");
}

if (empty($phone)) $phone = null;
if (empty($notify)) $notify = 0;

$password = null;
if (AuthHandler::getAuthMode() == AuthHandler::AUTH_MODE_PASS) {
    if (empty($passw1) || empty($passw2)) {
        $valid = false;
        $messages[] = _("Please fill in password and confirmation");        
    } else if ($passw1 !== $passw2) {
        $valid = false;
        $messages[] = _("Password and confirmation field does not match");           
    } else {
        // Valid
        $password = Utils::hashPassword($passw1);  
    } 
}

$isUpdateContact = ((AuthHandler::getRole() == ROLE_IDENTIFIED) || (AuthHandler::getRole() == ROLE_IDENTIFIED_REGISTERED));
$isUpdateRide = (AuthHandler::getRole() == ROLE_IDENTIFIED_REGISTERED);

$canUpdateEmail = (AuthHandler::getAuthMode() != AuthHandler::AUTH_MODE_LDAP);

$action = ($isUpdateRide) ? 'update' : 'add';

if ($valid) {

    $server = DatabaseHelper::getInstance();
    
    try {
    
        if ($isUpdateContact) {
            $contactId = AuthHandler::getLoggedInUserId();
        } else {
            $contactId = false;
        }
        
        if ($isUpdateRide) {
            $ride = $server->getRideProvidedByContactId($contactId);   
            $rideId = $ride['Id'];
        } else {
            $rideId = false;
        }
        
        // Put it all in a transaction - we might fail after a few successes
        $server->beginTransaction();
        
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

        try {
            if ($isUpdateContact) {
                $server->updateContact($contactId, $name, $phone, ($canUpdateEmail ? $email : null));
            } else {               
                // If it is a new ride - register this contact
                $contactId = $server->addContact($name, $phone, $email, $password);

                AuthHandler::authByContactId($contactId);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $messages []= _("This email address is already in use");
            }
            throw $e;
        }

        // Add or update ride
        if ($isUpdateRide) {
            if ($server->updateRide($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $wantTo, $notify)) {
                GlobalMessage::setGlobalMessage(_("Ride successfully updated."));
            } else {
                throw new Exception("Could not update ride");
            }
        } else {
            $rideId = $server->addRide($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $wantTo, $notify);
            if (!$rideId) {
            	throw new Exception("Could not add ride");
            }
            $mailBody = ViewRenderer::renderToString(VIEWS_PATH . '/registrationMail.php', array('contact' => $server->getContactById($contactId))); 
            Utils::sendMail(Utils::buildEmail($email), $name, getConfiguration('mail.addr'), getConfiguration('mail.display'), 'Carpool registration', $mailBody);          
        }
        
        // Finally, our new user gets their role
        AuthHandler::setRole(ROLE_IDENTIFIED_REGISTERED);
        
        $server->commit();
        
        if (!$isUpdateRide && getConfiguration('notify.immediate') == 1) {
            Service_ShowInterest::run($rideId);
        }
        
        echo json_encode(array('status' => 'ok', 'action' => $action));
    } catch (PDOException $e) {
        $server->rollBack();
        if ($e->getCode() == 23000) {
            // If this is a unique constraint problem - we want to display the correct message
            echo json_encode(array('status' => 'invalid', 'action' => $action, 'messages' => $messages));
        } else {            
            logException($e);
            echo json_encode(array('status' => 'err', 'action' => $action));
        }
    } catch (Exception $e) {
        $server->rollBack();
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