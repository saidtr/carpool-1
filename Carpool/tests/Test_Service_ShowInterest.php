<?php

require_once "testenv.php";

class Test_Service_ShowInterest extends PHPUnit_Framework_TestCase {

    function assertRidesContainIds($rides, $ids) {      
        // First test that the number actually match
        $this->assertEquals(count($rides), count($ids));
        
        // And make sure those are the actual IDs we expect
        $rideIds = array();
        foreach ($rides as $record) {
            $rideIds []= $record['Id'];
        }
        
        foreach ($ids as $id) {
            $this->assertContains($id, $rideIds);
        }
    }
    
    function assertMatchingResults($results, $expected, $msg = null) {
        // First test that the number actually match
        $this->assertEquals(count($expected), count($results), $msg);
        
        foreach ($expected as $exp => $expRides) {
            $this->assertTrue(isset($results[$exp]), $msg);
            $foundForThisRide = $results[$exp];
            $this->assertTrue(count(array_diff($foundForThisRide, $expRides)) === 0, $msg);
        }
    }

    function setUp() {
    }
    
    function tearDown() {    
    }
    
    function testFindPotentialRides() {
        TestUtils::clearDatabase();
        
        $ride1 = TestUtils::createSimpleRide(1, 2, STATUS_LOOKING);
        $ride2 = TestUtils::createSimpleRide(3, 4, STATUS_LOOKING);
        $ride3 = TestUtils::createSimpleRide(3, 4, STATUS_OFFERED);
        $ride4 = TestUtils::createSimpleRide(3, 4, STATUS_SHARING);
        
        // And one inactive
        $ride5 = TestUtils::createSimpleRide(5, 6, STATUS_OFFERED);
        DatabaseHelper::getInstance()->updateRideActive($ride5, RIDE_INACTIVE);
        
        // Grab all potential rides - we should now have both
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(1);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING, 1);         
        $this->assertRidesContainIds($potentials, array($ride3, $ride4));
        
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_OFFERED, 1);
        $this->assertRidesContainIds($potentials, array($ride1, $ride2, $ride4));

        $potentials = Service_ShowInterest::findPotentialRides(STATUS_SHARING, 1);       
        $this->assertRidesContainIds($potentials, array($ride1, $ride2, $ride3, $ride4));
     
        // Make sure we don't look in the wrong regions
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING, 2);     
        $this->assertEquals(0, count($potentials));
        
        // Now let's update the last run and make sure those rides won't be counted
        DatabaseHelper::getInstance()->updateLastShowInterestNotifier(time() + 1000);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING, 1);     
        $this->assertEquals(0, count($potentials));
    }
    
    function testFindRidesToNotify() {
        TestUtils::clearDatabase();
        
        $ride1 = TestUtils::createSimpleRide(1, 2, STATUS_LOOKING, 1);
        $ride2 = TestUtils::createSimpleRide(3, 4, STATUS_LOOKING, 0);
        $ride3 = TestUtils::createSimpleRide(1, 2, STATUS_OFFERED, 1);
        $ride4 = TestUtils::createSimpleRide(3, 4, STATUS_OFFERED, 0);
        $ride5 = TestUtils::createSimpleRide(5, 6, STATUS_LOOKING, 0);
        DatabaseHelper::getInstance()->updateRideActive($ride5, RIDE_INACTIVE);
        $ride6 = TestUtils::createSimpleRide(5, 6, STATUS_SHARING, 1);
        
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_LOOKING, 1);
        $this->assertRidesContainIds($toNotify, array($ride1));
        
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_OFFERED, 1);
        $this->assertRidesContainIds($toNotify, array($ride3));       

        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_SHARING, 1);
        $this->assertRidesContainIds($toNotify, array($ride6));               
    }
    
    /**
     * 
     * @depends testFindPotentialRides
     * @depends testFindRidesToNotify
     */
    function testSearchForMatchingRides() {
        TestUtils::clearDatabase();
        
        $ride1 = TestUtils::createSimpleRide(1, 2, STATUS_LOOKING);
        $ride2 = TestUtils::createSimpleRide(1, 3, STATUS_LOOKING);
        $ride3 = TestUtils::createSimpleRide(1, 2, STATUS_OFFERED);
        $ride4 = TestUtils::createSimpleRide(3, 4, STATUS_OFFERED);
        $ride5 = TestUtils::createSimpleRide(1, 7, STATUS_OFFERED);
        
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_LOOKING, 1);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING, 1);
        $matching = Service_ShowInterest::searchForMatchingRides($potentials, $toNotify);
        
        $expectedResults = array (
            $ride1 => array($ride3)
        );
        $this->assertMatchingResults($matching, $expectedResults, "TestSearchForMatchingRides: Test 1");
        
        // Now, see what happens with don't-cares
        $ride6 = TestUtils::createSimpleRide(1, LOCATION_DONT_CARE, STATUS_LOOKING);
        $ride7 = TestUtils::createSimpleRide(LOCATION_DONT_CARE, 7, STATUS_LOOKING);
        
        // One for the sharing
        $ride8 = TestUtils::createSimpleRide(1, 2, STATUS_SHARING);
        
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_LOOKING, 1);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_LOOKING, 1);
        $matching = Service_ShowInterest::searchForMatchingRides($potentials, $toNotify);
        
        // We should now have 3 results:
        //   ride1: ride3, ride8
        //   ride6: ride3, ride5, ride8
        //   ride7: ride5
        $expectedResults = array (
            $ride1 => array($ride3, $ride8),
            $ride6 => array($ride3, $ride5, $ride8),
            $ride7 => array($ride5)
        );
        $this->assertMatchingResults($matching, $expectedResults, "TestSearchForMatchingRides: Test 2");
        
        // Now test the other way
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_OFFERED, 1);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_OFFERED, 1);
        $matching = Service_ShowInterest::searchForMatchingRides($potentials, $toNotify);
        
        // We should now have (wildcards in the rides to notify are not supported yet):
        //   ride3: ride1
        // TODO: Implement support for wildcards on the other way as well
        $expectedResults = array (
            $ride3 => array($ride1, $ride8)
        );
        $this->assertMatchingResults($matching, $expectedResults, "TestSearchForMatchingRides: Test 3");

        // And what happens when they share
        $toNotify = Service_ShowInterest::findRidesToNotify(STATUS_SHARING, 1);
        $potentials = Service_ShowInterest::findPotentialRides(STATUS_SHARING, 1);
        $matching = Service_ShowInterest::searchForMatchingRides($potentials, $toNotify);
        
        // We should now have the following results:
        //   ride8: ride1, ride3
        $expectedResults = array (
            $ride8 => array($ride3, $ride1)
        );
        $this->assertMatchingResults($matching, $expectedResults, "TestSearchForMatchingRides: Test 4");
    }
    
    
}