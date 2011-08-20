<?php

include '../tests/testenv.php';

/*
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Carpool</title>
</head>
<body>
<?php 
*/

//$rides = DatabaseHelper::getInstance()->searchRides(array());
//echo MailHelper::render(VIEWS_PATH . '/showInterestMail.php', array('rides' => $rides));

$contact = DatabaseHelper::getInstance()->getContactById(1);
echo MailHelper::render(VIEWS_PATH . '/registrationMail.php', array('contact' => $contact));

?>
