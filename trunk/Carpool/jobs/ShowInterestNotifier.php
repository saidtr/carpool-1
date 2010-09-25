<?php

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

$rideId = null;
if (isset($_GET['rideId'])) {
    $rideId = (int) $_GET['rideId'];
} 

Logger::info('Show interest job started' . ($rideId !== null) ? ' - only check ride ' . $rideId : ' - checking all rides');

Service_ShowInterest::run($rideId);

Logger::info('Show interest job terminated');

