<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

if (ENV !== ENV_DEVELOPMENT && (!Utils::IsXhrRequest() || !AuthHandler::isSessionExisting())) {
    die();
}

extract($_GET, EXTR_SKIP);
if (!isset($regionId)) {
    die();
}

try {
    $regionConfiguration = RegionManager::getInstance()->getRegionConfiguration($regionId);
    $cities = DatabaseHelper::getInstance()->getCities($regionId);
    if ($regionConfiguration !== false) {
        $res = array('status' => 'ok', 'results' => 
            array(
            	'regionConfiguration' => $regionConfiguration,
                'cities' => $cities
            )
        );
    } else {
        warn("Could not find configuration for region $regionId");
        $res = array('status' => 'err', 'msg' => _("Region not found"));
    }
} catch(Exception $e) {
    logException($e);
    $res = array('status' => 'err', 'msg' => _("Internal Error"));
}


echo json_encode($res);

