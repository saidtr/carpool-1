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
    
    
    public static function run($rideId = null) {
        info('ShowInterestNotifier: started');
        
        $db = DatabaseHelper::getInstance();
        
        $searchParams = array();
        $searchParams['status'] = STATUS_OFFERED;
        
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
        
        // List all rides and create an index.
        // For each ride from X to Y, we create the following pointer:
        // X to Y -> Ride ID
        // In addition, to support wildcard searches, we add 2 more pointers:
        // X to * -> Ride ID
        // * to Y -> Ride ID 
        $rideIdx = array();
        foreach ($allRides as $ride) {
            $index = self::buildIndexStr($ride['SrcCityId'], $ride['DestCityId']);
            // Put two additional indexes to fit "wild card" search (location = everywhere)
            $indexWildCardFrom = self::buildIndexStr(LOCATION_DONT_CARE, $ride['DestCityId']);
            $indexWildCardTo = self::buildIndexStr($ride['SrcCityId'], LOCATION_DONT_CARE);
            if (!isset($fromIdx[$index])) {
                $fromIdx[$index] = array();
            }
            $fromIdx[$index] []= $ride['Id'];
            if (!isset($fromIdx[$indexWildCardFrom])) {
                $fromIdx[$indexWildCardFrom] = array();
            }
            $fromIdx[$indexWildCardFrom] []= $ride['Id'];
            if (!isset($fromIdx[$indexWildCardTo])) {
                $fromIdx[$indexWildCardTo] = array();
            }
            $fromIdx[$indexWildCardTo] []= $ride['Id'];
            
        }
        
        // Now, use the index we created before to find matching rides
        // for all users who showed interest in a specific ride.
        $results = array();
        $toNotifyRides = $db->searchRides(array('status' => STATUS_LOOKING));
        foreach ($toNotifyRides as $ride) {
            $index = self::buildIndexStr($ride['SrcCityId'], $ride['DestCityId']); 
            if (isset($fromIdx[$index])) {
                if (!isset($results[$ride['ContactId']])) {
                    $results[$ride['ContactId']] = array();
                }
                $results[$ride['ContactId']] = array_merge($results[$ride['ContactId']], $fromIdx[$index]);
            } 
        }
        
        foreach ($results as $contactId => $potentialResults) {
            self::notify($contactId, $allRides, $potentialResults);            
        }
        
        $db->updateLastShowInterestNotifier(time());
        
        info('ShowInterestNotifier: done');
    }

}