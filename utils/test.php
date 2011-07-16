<?php

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Carpool</title>
</head>
<body>
<?php 

$rides = DatabaseHelper::getInstance()->searchRides(array('status' => STATUS_OFFERED));
$contact = DatabaseHelper::getInstance()->getContactById(1);

echo MailHelper::render(VIEWS_PATH . '/showInterestMail.php', array('rides' => $rides), $contact);

echo MailHelper::render(VIEWS_PATH . '/registrationMail.php', null, $contact);