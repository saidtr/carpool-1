<?php

class Service_ShowInterest {
    
    private static function buildIndexStr($from, $to) {
    	return ($from == LOCATION_DONT_CARE ? '*' : $from) . '-' . ($to == LOCATION_DONT_CARE ? '*' : $to);
    }
    
    private static function notify($contactId, &$allRides, $potentialRideIds) {
        debug(__METHOD__ . "($contactId, " . json_encode($potentialRideIds) . ")");
        $toNotify = array();
        foreach ($allRides as $ride) {
            if (in_array($ride['Id'], $potentialRideIds)) {
                $toNotify []= $ride;
            }
        }   
               
        $contact = DatabaseHelper::getInstance()->getContactById($contactId);
        $mailBody = MailHelper::render(VIEWS_PATH . '/showInterestMail.php', array('rides' => $toNotify), $contact);
        Utils::sendMail(Utils::buildEmail($contact['Email']), $contact['Email'], getConfiguration('mail.addr'), getConfiguration('mail.display'), 'New rides from carpool', $mailBody);
    }
    
    // Returns the "opposite" status for the status provided, where the meaning of 
    // opposite is - ride status that will be a potential match for the provided one.
    // For example: the opposite of "looking" is "providing" and "sharing"
    private static function getOppositeStatus($status) {
        switch($status) {
            case STATUS_LOOKING: return array(STATUS_OFFERED, STATUS_SHARING); 
            case STATUS_OFFERED: return array(STATUS_LOOKING, STATUS_SHARING); 
            case STATUS_SHARING: return array(STATUS_LOOKING, STATUS_OFFERED, STATUS_SHARING);
            // Should never get here!
            default: assert(false); 
        }        
        return false;
    }
    
    // This function returns the list of all rides that might be relevant
    // to rides with the provided status.
    public static function findPotentialRides($status) {
        $db = DatabaseHelper::getInstance();
        
        $searchParams = array();
        $searchParams['status'] = self::getOppositeStatus($status);
        
        $lastRun = $db->getLastShowInterestNotifier();
        if ($lastRun !== false && $lastRun > 0) {
            info('Last run was at ' . $lastRun);
            $searchParams['minTimeCreated'] = $lastRun;
        } else {
            info('No last run found, we go from the start');
        }
        
        $allRides = $db->searchRides($searchParams);

        return $allRides;
    }
    
    // This function returns the list of rides that we want to notify.
    // Practically this means: rides with the provided status, who asked to
    // be notified
    public static function findRidesToNotify($status) {
        $searchParams = array(
        	'notify' => 1,
            'status' => $status
        );
        
        return DatabaseHelper::getInstance()->searchRides($searchParams);
    }
    
    public static function searchForMatchingRides(&$potentialRides, &$ridesToNotify) {
        // List all rides and create an index.
        // For each ride from X to Y, we create the following pointer:
        // X to Y -> Ride ID
        // In addition, to support wildcard searches, we add 2 more pointers:
        // X to * -> Ride ID
        // * to Y -> Ride ID 
        $rideIdx = array();
        foreach ($potentialRides as $ride) {
            $index = self::buildIndexStr($ride['SrcCityId'], $ride['DestCityId']);
            // Put two additional indexes to fit "wild card" search (location = everywhere)
            $indexWildCardFrom = $ride['DestCityId'] != LOCATION_DONT_CARE ? self::buildIndexStr(LOCATION_DONT_CARE, $ride['DestCityId']) : false;
            $indexWildCardTo = $ride['SrcCityId'] != LOCATION_DONT_CARE ? self::buildIndexStr($ride['SrcCityId'], LOCATION_DONT_CARE) : false;
            if (!isset($rideIdx[$index])) {
                $rideIdx[$index] = array();
            }
            if (array_search($ride['Id'], $rideIdx[$index]) === false)
                $rideIdx[$index] []= $ride['Id'];
            
            if ($indexWildCardFrom) {
                if (!isset($rideIdx[$indexWildCardFrom])) {
                    $rideIdx[$indexWildCardFrom] = array();
                }
                if (array_search($ride['Id'], $rideIdx[$indexWildCardFrom]) === false)
                    $rideIdx[$indexWildCardFrom] []= $ride['Id'];
            }
            if ($indexWildCardTo) {
                if (!isset($rideIdx[$indexWildCardTo])) {
                    $rideIdx[$indexWildCardTo] = array();
                }
                if (array_search($ride['Id'], $rideIdx[$indexWildCardTo]) === false)
                    $rideIdx[$indexWildCardTo] []= $ride['Id'];
            }          
        }
        
        // Now, use the index we created before to find matching rides
        // for all users who showed interest in a specific ride.
        $results = array();
        foreach ($ridesToNotify as $ride) {
            $index = self::buildIndexStr($ride['SrcCityId'], $ride['DestCityId']);
            if (isset($rideIdx[$index])) {
                if (!isset($results[$ride['ContactId']])) {
                    $results[$ride['ContactId']] = array();
                }
                $results[$ride['ContactId']] = array_unique(array_merge($results[$ride['ContactId']], $rideIdx[$index]));
                // Now make sure that contact is not shown in their own result list
                // (could happen for rides with "Sharing" status)
                if (($pos = array_search($ride['ContactId'], $results[$ride['ContactId']])) !== false) {
                    unset ($results[$ride['ContactId']][$pos]);
                    
                }
                
            } 
        }    

        return $results;
    } 
    
    public static function run($rideId = null) {
        info('ShowInterestNotifier: started');
        
        if ($rideId === null) {
            $statuses = array(STATUS_LOOKING, STATUS_OFFERED, STATUS_SHARING);
            foreach ($statuses as $status) {
                $potentialRides = self::findPotentialRides($status);
                $ridesToNotify = self::findRidesToNotify($status);
                
                $results = self::searchForMatchingRides($potentialRides, $ridesToNotify);
                     
                foreach ($results as $contactId => $potentialResults) {
                    self::notify($contactId, $potentialRides, $potentialResults);            
                }
            }
        } else {
            $newRide = array(0 => DatabaseHelper::getInstance()->getRideById($rideId));
            $newRideStatus = $newRide[0]['Status'];
            $ridesToNotify = self::findRidesToNotify(self::getOppositeStatus($newRideStatus));

            $results = self::searchForMatchingRides($newRide, $ridesToNotify);
             
            foreach ($results as $contactId => $potentialResults) {
                self::notify($contactId, $newRide, $potentialResults);
            }
        }
        
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(time());
        
        info('ShowInterestNotifier: done');
    }

}