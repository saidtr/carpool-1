<?php 

// Just dump the contents of the ride and the contact tables

include '../public/env.php';
include APP_PATH . '/Bootstrap.php';

$db = DatabaseHelper::getConnection();

$rs = $db->query('SELECT * FROM Ride');
$res = $rs->fetchAll(PDO::FETCH_ASSOC);

echo '<h1>Rides</h1>';
foreach ($res as $ride) {
    var_dump($ride);
}

$rs = $db->query('SELECT * FROM Contacts');
$res = $rs->fetchAll(PDO::FETCH_ASSOC);

echo '<h1>Contacts</h1>';
foreach ($res as $contact) {
    var_dump($contact);
}

echo '<h1>ShowInterestNotifier</h1>';
$rs = $db->query('SELECT * FROM ShowInterestNotifier');
$res = $rs->fetchAll(PDO::FETCH_ASSOC);
var_dump($res);