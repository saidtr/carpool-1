<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && (!Utils::IsXhrRequest() || !AuthHandler::isSessionExisting())) {
    die();
}

extract($_GET, EXTR_SKIP);

$params = array();

if (isset($srcCityId) && !empty($srcCityId) && !($srcCityId == LOCATION_DONT_CARE)) {
	info("SrcCityId $srcCityId");
    $params['srcCityId'] = $srcCityId;
}

if (isset($destCityId) && !empty($destCityId) && !($destCityId == LOCATION_DONT_CARE)) {
    $params['destCityId'] = $destCityId;
}

if (isset($wantTo) && !empty($wantTo)) {
    $params['status'] = array($wantTo, STATUS_SHARING);
} else {
    // We need to avoid displaying of inactive rides
    $params['status'] = array(STATUS_LOOKING, STATUS_OFFERED, STATUS_SHARING);
}

$server = DatabaseHelper::getInstance();

$res = array('status' => 'ok', 'results' => $server->searchRides($params));

echo json_encode($res);
