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
    
    public static function findPotentialRides($status) {
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
        
        $allRides = $db->searchRides($searchParams);

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
            } 
        }    

        return $results;
    } 
    
    
    public static function run($rideId = null) {
        info('ShowInterestNotifier: started');
        
        if ($rideId === null) {
            $status1 = STATUS_OFFERED;
            $status2 = STATUS_LOOKING;
            for ($i = 0; $i < 2; $status1 = STATUS_LOOKING, $status2 = STATUS_OFFERED, ++$i) {
                $potentialRides = self::findPotentialRides($status1);
                $ridesToNotify = self::findRidesToNotify($status2);
                
                $results = self::searchForMatchingRides($potentialRides, $ridesToNotify);
                     
                foreach ($results as $contactId => $potentialResults) {
                    self::notify($contactId, $potentialRides, $potentialResults);            
                }
            }
        } else {
            $newRide = array(0 => DatabaseHelper::getInstance()->getRideById($rideId));
            $newRideStatus = $newRide[0]['Status'];
            $ridesToNotify = self::findRidesToNotify($newRideStatus == STATUS_LOOKING ? STATUS_OFFERED : STATUS_LOOKING);

            $results = self::searchForMatchingRides($newRide, $ridesToNotify);
             
            foreach ($results as $contactId => $potentialResults) {
                self::notify($contactId, $newRide, $potentialResults);
            }
        }
        
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(time());
        
        info('ShowInterestNotifier: done');
    }

}