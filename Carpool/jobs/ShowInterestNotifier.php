<?php

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

Logger::info('Show interest job started');

$rideId = null;
if (isset($_GET['rideId'])) {
    $rideId = (int) $_GET['rideId'];
} 

Service_ShowInterest::run($rideId);

Logger::info('Show interest job terminated');

