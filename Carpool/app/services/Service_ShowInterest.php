<?php

class Service_ShowInterest {
    
    private static function buildIndexStr($from, $to) {
    	return ($from == LOCATION_DONT_CARE ? '*' : $from) . '-' . ($to == LOCATION_DONT_CARE ? '*' : $to);
    }
    
    private static function notify($rideId, $potentialRideIds) {
    	Logger::debug(__METHOD__ . "($rideId, " . json_encode($potentialRideIds) . ")");
    }
    
    public static function run() {
        Logger::info('ShowInterestNotifier: started');
        
        $db = DatabaseHelper::getInstance();
        
        $lastRun = $db->getLastShowInterestNotifier();
        
        $searchParams = array();
        $searchParams['status'] = STATUS_OFFERED;
        
        if ($lastRun['LastRun']) {
        	Logger::info('Last run was at ' . $lastRun['LastRun']);
        	$searchParams['minTimeUpdated'] = $lastRun['LastRun'];
        } else {
        	Logger::info('No last run found, we go from the start');
        }
        
        $allRides = $db->searchRides($searchParams);
        
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
        var_dump($fromIdx);
        
        $results = array();
        $toNotifyRides = $db->searchRides(array('status' => STATUS_LOOKING));
        foreach ($toNotifyRides as $ride) {
        	$index = self::buildIndexStr($ride['SrcCityId'], $ride['DestCityId']); 
        	if (isset($fromIdx[$index])) {
        	    if (!isset($results[$ride['Id']])) {
        	        $results[$ride['Id']] = array();
        	    } 
        	    $results[$ride['Id']] []= $fromIdx[$index];
        	} 
        }
        
        var_dump($results);
        
        foreach ($results as $result) {
            
        }
        
        // TODO: Handle contact who wants more than a single ride
        // TODO: Update last run
        
        Logger::info('ShowInterestNotifier: done');
    }

}