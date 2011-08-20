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
    $cities = DatabaseHelper::getInstance()->getCities($regionId);
    if ($cities !== false) {
        $res = array('status' => 'ok', 'results' => $cities);
    } else {
        warn("Could not fetch cities list for region $regionId");
        $res = array('status' => 'err', 'msg' => _("Could not fetch cities list"));
    }

} catch(Exception $e) {
    logException($e);
    $res = array('status' => 'err', 'msg' => _("Internal Error"));
}


echo json_encode($res);

