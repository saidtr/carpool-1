<?php

/**
 * 
 * Send notification mails about new possible rides to all people
 * in the "show interest" list, by invoking the ShowInterest service.
 * 
 * This script should run using a scheduling mechanism such as
 * cron; it is not required in case the application is configured to
 * send immediate notifications. In other words, it should only run if
 * the configuration key notify.immediate is not set 
 * 
 * @author itay
 * 
 */


require '../public/env.php';
require APP_PATH . '/Bootstrap.php';

$rideId = null;
if (isset($_GET['rideId'])) {
    $rideId = (int) $_GET['rideId'];
} 

info('Show interest job started' . (($rideId !== null) ? ' - only check ride ' . $rideId : ' - checking all rides'));

Service_ShowInterest::run($rideId);

info('Show interest job terminated');

