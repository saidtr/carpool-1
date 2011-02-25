<?php

define ('CONF_FILE', 'carpool_test.ini');

require_once '../public/env.php';
require_once APP_PATH . '/Bootstrap.php';

class TestUtils {
    
    private static $ridesCounter = 0;

    // Delete all the dynamic data from the database
    static function clearDatabase() {
        $con = DatabaseHelper::getConnection();
        $con->query('DELETE From Ride');
        $con->query('DELETE From Contacts');
        $con->query('UPDATE ShowInterestNotifier SET LastRun = 0');
    }
    
    static function createSimpleRide($from, $to, $status, $notify = 1) {
        $db = DatabaseHelper::getInstance();
        
        $testContact = $db->addContact('test' . self::$ridesCounter, '1234', 'test' . self::$ridesCounter . '@test.com');
        if (!$testContact) {
            return false;
        }
        $testRide = $db->addRide($from, 'city_' . $from, $to, 'city_' . $to, TIME_IRRELEVANT, TIME_IRRELEVANT, $testContact, '', $status, $notify);
        if (!$testRide) {
            return false;
        }
        
        ++self::$ridesCounter;
        
        return $testRide;
    }

}