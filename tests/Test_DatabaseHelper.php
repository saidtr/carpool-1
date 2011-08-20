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
        
        $testRide1 = $this->dbh->addRide(1, 'city_1', 2, 'city_2', TIME_IRRELEVANT, TIME_IRRELEVANT, $testContact, '', RIDE_ACTIVE, 0, 1);
        if (!$testRide1) {
            $this->fail('addRide failed');
        }
        $count = $this->dbh->countRidesForContactId($testContact);
        $this->assertEquals(1, $count);
        $testRide2 = $this->dbh->addRide(1, 'city_1', 2, 'city_2', TIME_IRRELEVANT, TIME_IRRELEVANT, $testContact, '', RIDE_INACTIVE, 0, 1);
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
    
    public function testGetRegionConfigration() {
        DatabaseHelper::getConnection()->query('DELETE FROM Regions');
        $this->dbh->insert('Regions', array(
            'Name' => 'Region1',
            'Abbrev' => 'rg1',
            'DefaultSrcCityId' => 1,
            'DefaultSrcLocation' => 'DefaultSrc1'
        ));
        $id = DatabaseHelper::getConnection()->lastInsertId();
        
        $conf = $this->dbh->getRegionConfiguration($id);
        $this->assertNotNull($conf);
        $this->assertEquals(1, $conf['DefaultSrcCityId']);   
        $this->assertEquals('DefaultSrc1', $conf['DefaultSrcLocation']);   
        $this->assertNull($conf['DefaultDestLocation']);
        
        $this->dbh->insert('Regions', array(
            'Name' => 'Region2',
            'Abbrev' => 'rg2',
            'DefaultSrcCityId' => 71,
            'DefaultSrcLocation' => 'DefaultSrc2',
        	'DefaultDestCityId' => 70,
        	'DefaultDestLocation' => 'DefaultDest2'
        
        ));
        $id = DatabaseHelper::getConnection()->lastInsertId();
        
        $conf = $this->dbh->getRegionConfiguration($id);
        $this->assertNotNull($conf);
        $this->assertEquals(71, $conf['DefaultSrcCityId']);   
        $this->assertEquals('DefaultSrc2', $conf['DefaultSrcLocation']);   
        $this->assertEquals(70, $conf['DefaultDestCityId']);   
        $this->assertEquals('DefaultDest2', $conf['DefaultDestLocation']);   
    }
    
    
}