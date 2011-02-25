<?php

class Service_ShowInterest {
    
    private static function buildIndexStr($from, $to) {
    	return ($from == LOCATION_DONT_CARE ? '*' : $from) . '-' . ($to == LOCATION_DONT_CARE ? '*' : $to);
    }
    
    private static function notify($contactId, $allRides, $potentialRideIds) {
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
    
    public static function findPotentialRides($status, $rideId = null) {
        $db = DatabaseHelper::getInstance();
        
        $searchParams = array();
        $searchParams['status'] = $status;
        
        $lastRun = $db->getLastShowInterestNotifier();
        if ($lastRun !== false && $lastRun > 0) {
            info('Last run was at ' . $lastRun);
            $searchParams['minTimeCreated'] = $lastRun;
        } else {
            info('No last run found, we go from the start');
        }
        
        if ($rideId == null) {
            $allRides = $db->searchRides($searchParams);
        } else {
            $allRides = array(0 => $db->getRideById($rideId));
        }

        return $allRides;
    }
    
    public static function findRidesToNotify($status) {
        $searchParams = array(
            'status' => $status,
            'notify' => 1    
        );
        
        return DatabaseHelper::getInstance()->searchRides($searchParams);
    }
    
    public static function searchForMatchingRides($potentialRides, $ridesToNotify) {
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
            $indexWildCardFrom = self::buildIndexStr(LOCATION_DONT_CARE, $ride['DestCityId']);
            $indexWildCardTo = self::buildIndexStr($ride['SrcCityId'], LOCATION_DONT_CARE);
            if (!isset($rideIdx[$index])) {
                $rideIdx[$index] = array();
            }
            $rideIdx[$index] []= $ride['Id'];
            if (!isset($rideIdx[$indexWildCardFrom])) {
                $rideIdx[$indexWildCardFrom] = array();
            }
            $rideIdx[$indexWildCardFrom] []= $ride['Id'];
            if (!isset($rideIdx[$indexWildCardTo])) {
                $rideIdx[$indexWildCardTo] = array();
            }
            $rideIdx[$indexWildCardTo] []= $ride['Id'];          
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
                $results[$ride['ContactId']] = array_merge($results[$ride['ContactId']], $rideIdx[$index]);
            } 
        }       

        return $results;
    } 
    
    
    public static function run($rideId = null) {
        info('ShowInterestNotifier: started');
        
        $potentialRides = self::findPotentialRides(STATUS_OFFERED, $rideId);
        $ridesToNotify = self::findRidesToNotify(STATUS_LOOKING);
        
        $results = self::searchForMatchingRides($potentialRides, $ridesToNotify);
             
        foreach ($results as $contactId => $potentialResults) {
            self::notify($contactId, $allRides, $potentialResults);            
        }
        
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(time());
        
        info('ShowInterestNotifier: done');
    }

}