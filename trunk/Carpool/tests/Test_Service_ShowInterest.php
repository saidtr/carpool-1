<?php

require_once "testenv.php";
require_once "PHPUnit.php";

class Test_Service_ShowInterest extends PHPUnit_TestCase {
    
    function setUp() {
        TestUtils::clearDatabase();        
    }
    
    function tearDown() {
        
    }
    
    function testFindPotentialRides() {
        $ride1 = TestUtils::createSimpleRide(1, 2, STATUS_LOOKING);
        $ride2 = TestUtils::createSimpleRide(3, 4, STATUS_LOOKING);
        
        // And one inactive
        $ride3 = TestUtils::createSimpleRide(5, 6, STATUS_OFFERED);
        DatabaseHelper::getInstance()->updateRideActive($ride3, RIDE_INACTIVE);
        
        if (!$ride1 || !$ride2) {
            $this->fail('Failed to create test data');
        }
        
        // Grab all potential rides - we should now have both
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(0);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING);       
        $this->assertEquals(2, count($potentials));
        
        // And make sure those are the actual IDs we expect
        $rideIds = array();
        foreach ($potentials as $record) {
            $rideIds []= $record['Id'];
        }
        
        $this->assertContains($ride1, $rideIds);
        $this->assertContains($ride2, $rideIds);
        
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_OFFERED);
        $this->assertEquals(0, count($potentials));

        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING);       
        $this->assertEquals(2, count($potentials));
     
        // Now let's update the last run and make sure those rides won't be counted
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(time() + 1000);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING);     
        $this->assertEquals(0, count($potentials));
        
    }
    
    
}