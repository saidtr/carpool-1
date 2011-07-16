<?php

require_once "testenv.php";
require_once "PHPUnit.php";

class Test_DatabaseHelper extends PHPUnit_TestCase {
    
    private $dbh;
    
    public function setUp() {
        $this->dbh = DatabaseHelper::getInstance();
    }
    
    public function testCountRidesForContactId() {
        TestUtils::clearDatabase(); 
        $testContact = $this->dbh->addContact('test1', '1234', 'test1@test.com', ROLE_IDENTIFIED_REGISTERED);
        if (!$testContact) {
            $this->fail('addContact failed');
        }
        
        $count = $this->dbh->countRidesForContactId($testContact);
        $this->assertEquals(0, $count);
        
        $testRide1 = $this->dbh->addRide(1, 'city_1', 2, 'city_2', TIME_IRRELEVANT, TIME_IRRELEVANT, $testContact, '', RIDE_ACTIVE, 0);
        if (!$testRide1) {
            $this->fail('addRide failed');
        }
        $count = $this->dbh->countRidesForContactId($testContact);
        $this->assertEquals(1, $count);
        $testRide2 = $this->dbh->addRide(1, 'city_1', 2, 'city_2', TIME_IRRELEVANT, TIME_IRRELEVANT, $testContact, '', RIDE_INACTIVE, 0);
        if (!$testRide2) {
            $this->fail('addRide failed');
        }
        // Inactive rides also count
        $count = $this->dbh->countRidesForContactId($testContact);
        $this->assertEquals(2, $count);
        
        // Now, make sure we get 0 for non-existent contact
        $count = $this->dbh->countRidesForContactId($testContact + 1);
        $this->assertEquals(0, $count);
    }
    
    
}