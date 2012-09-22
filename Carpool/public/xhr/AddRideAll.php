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

$contactId = AuthHandler::getLoggedInUserId();
// If this contact already exists, it must be an update
$isUpdateContact = ($contactId !== false);
// If there are any rides assigned with this contact, it is an update 
$isUpdateRide = AuthHandler::isRideRegistered();

// XXX: Policy or something like that for the auth handler
$canUpdateEmail = (AuthHandler::getAuthMode() != AuthHandler::AUTH_MODE_LDAP);

$action = ($isUpdateRide) ? 'update' : 'add';

if (RegionManager::getInstance()->isMultiRegion()) {
	if (!RegionManager::getInstance()->isValidRegion($region)) {
		$messages[] = _("Invalid region");
		$valid = false;
	}
} else {
	$region = RegionManager::getInstance()->getDefaultRegion();
}

if ($valid) {

    $db = DatabaseHelper::getInstance();
    
    try {
        
        if ($isUpdateRide) {
            $ride = $db->getRideProvidedByContactId($contactId);   
            $rideId = $ride['Id'];
        } else {
            $rideId = false;
        }
        
        // Put it all in a transaction - we might fail after a few successes
        $db->beginTransaction();
        
        // Add destination and source city in case we don't have them in the DB
        // Assumes we already verified that the names are not empty
        
        if ($destCityId == LOCATION_NOT_FOUND) {
            $destCityId = $db->addCity($destCity, $region);
            if (!$destCity) {
            	throw new Exception("Could not insert city $destCity");
            }
        }
        
        if ($srcCityId == LOCATION_NOT_FOUND) {
            // Now need make sure that we didn't already insert this city
            if ($srcCity !== $destCity) {
                $srcCityId = $db->addCity($srcCity, $region);
                if (!$srcCityId) {
                    throw new Exception("Could not insert city $destCity");
                }
            } else {
                $srcCityId = $destCityId;
            }
        }
        
        // Update the region
        if (!RegionManager::getInstance()->setRegion($region)) {
            throw new Exception("Failed to update region");
        }
        

        try {
            if ($isUpdateContact) {
                $updateParams = array('name' => $name, 'phone' => $phone);
                // In some scenarios, contact might exist before having a ride - 
                // we need to set their role now 
                $currentRole = AuthHandler::getRole();
                if ($currentRole == ROLE_IDENTIFIED) {
                    $updateParams['role'] = ROLE_IDENTIFIED_REGISTERED;
                    AuthHandler::setRole(ROLE_IDENTIFIED_REGISTERED);
                }
                $updateParams['email'] = ($canUpdateEmail ? $email : null);
                $db->updateContact($updateParams, $contactId);
            } else {               
                // If it is a new ride - register this contact
                $contactId = $db->addContact($name, $phone, $email, ROLE_IDENTIFIED_REGISTERED, $password);
                AuthHandler::authByContactId($contactId);
                AuthHandler::setRole(ROLE_IDENTIFIED_REGISTERED);
            }
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $messages []= _("This email address is already in use");
            }
            throw $e;
        }

        // Add or update ride
        $rideParams = array(
            'SrcCityId'     => $srcCityId,
            'SrcLocation'   => $srcLocation,
            'DestCityId'    => $destCityId,
            'DestLocation'  => $destLocation,
            'TimeMorning'   => $timeMorning,
            'TimeEvening'   => $timeEvening,
            'Comment'       => $comment,
            'Notify'        => $notify,
            'Status'        => $wantTo,
            'Region'        => $region
        );
        if ($isUpdateRide) {
            if ($db->updateRide($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $wantTo, $notify, $region)) {
                GlobalMessage::setGlobalMessage(_("Ride successfully updated."));
            } else {
                throw new Exception("Could not update ride");
            }
        } else {
            $rideId = $db->addRide($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $wantTo, $notify, $region);
            if (!$rideId) {
            	throw new Exception("Could not add ride");
            }
            AuthHandler::updateRegisteredRideStatus(true);
            $mailBody = MailHelper::render(VIEWS_PATH . '/registrationMail.php', array('contact' => $db->getContactById($contactId)));
            Utils::sendMail(Utils::buildEmail($email), $name, getConfiguration('mail.addr'), getConfiguration('mail.display'), getConfiguration('app.name') . ' Registration', $mailBody);        
        }
        
        $db->commit();
        
        // XXX: Should show interest even if it's update?
        if (!$isUpdateRide && getConfiguration('notify.immediate') == 1) {
            Service_ShowInterest::run($rideId);
        }
        
        echo json_encode(array('status' => 'ok', 'action' => $action));
    } catch (PDOException $e) {
        $db->rollBack();
        if ($e->getCode() == 23000) {
            // If this is a unique constraint problem - we want to display the correct message
            echo json_encode(array('status' => 'invalid', 'action' => $action, 'messages' => $messages));
        } else {            
            logException($e);
            echo json_encode(array('status' => 'err', 'action' => $action));
        }
    } catch (Exception $e) {
        $db->rollBack();
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