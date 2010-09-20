<?php

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

function buildIndexStr($from, $to) {
	return $from . '-' . $to;
}

function notify($rideId, $potentialRideId) {
	Logger::debug(__METHOD__ . "($rideId, $potentialRideId)");
}

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

//var_dump($allRides);
$rideIdx = array();
foreach ($allRides as $ride) {
	$index = buildIndexStr($ride['SrcCityId'], $ride['DestCityId']); 
	$fromIdx[$index] = $ride['Id']; 
}
var_dump($fromIdx);

$toNotifyRides = $db->searchRides(array('status' => STATUS_LOOKING));
foreach ($toNotifyRides as $ride) {
	$index = buildIndexStr($ride['SrcCityId'], $ride['DestCityId']); 
	if (isset($fromIdx[$index])) {
		notify($ride['Id'], $fromIdx[$index]);
	} 
}

// TODO: Update last run

Logger::info('ShowInterestNotifier: done');

