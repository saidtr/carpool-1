<?php

require_once "testenv.php";
require_once "PHPUnit.php";


class Test_SimpleAcl extends PHPUnit_TestCase {

    function setUp() {
        
    }
    
    function tearDown() {
        
    }
    
    function testSingleRole() {
        $acl = new SimpleAcl();
        $acl->addRole(ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED);
        $acl->addResource(ROLE_GUEST, 'resource1');
        $acl->addResource(ROLE_GUEST, 'resource2');
        $acl->addResource(ROLE_GUEST, array('resource3', 'resource4'));
        
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource3'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource4'));
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource5'));
        $this->assertFalse($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
    }

    function testAddResourceTwice() {
        $acl = new SimpleAcl();
        $acl->addRole(ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED);
        $acl->addResource(ROLE_GUEST, array('resource1', 'resource2'));
        $acl->addResource(ROLE_GUEST, 'resource3');
        $acl->addResource(ROLE_IDENTIFIED, array('resource1', 'resource2'));
        $acl->addResource(ROLE_IDENTIFIED, 'resource3');

        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource3'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource3'));
    }
    
    function testSimpleHierarchy() {
        $acl = new SimpleAcl();
        $acl->addRole(ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED, ROLE_GUEST);
        $acl->addResource(ROLE_GUEST, array('resource1', 'resource2'));
        $acl->addResource(ROLE_IDENTIFIED, array('resource3', 'resource4'));
        
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource3'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource4'));
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource3'));
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource4'));        
        
    }
    
    function testMultipleHierarchy() {
        $acl = new SimpleAcl();
        
        $acl->addRole(ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED, ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED_REGISTERED, ROLE_IDENTIFIED);
        $acl->addRole(ROLE_ADMINISTRATOR, ROLE_IDENTIFIED);
        $acl->addResource(ROLE_GUEST, 'resource1');
        $acl->addResource(ROLE_IDENTIFIED, 'resource2');
        $acl->addResource(ROLE_IDENTIFIED_REGISTERED, 'resource_personal');
        $acl->addResource(ROLE_ADMINISTRATOR, 'resource_important');
        
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED_REGISTERED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_ADMINISTRATOR, 'resource1'));

        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED_REGISTERED, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_ADMINISTRATOR, 'resource2'));

        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource_personal'));
        $this->assertFalse($acl->isAllowed(ROLE_IDENTIFIED, 'resource_personal'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED_REGISTERED, 'resource_personal'));
        $this->assertFalse($acl->isAllowed(ROLE_ADMINISTRATOR, 'resource_personal'));
        
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource_important'));
        $this->assertFalse($acl->isAllowed(ROLE_IDENTIFIED, 'resource_important'));
        $this->assertFalse($acl->isAllowed(ROLE_IDENTIFIED_REGISTERED, 'resource_important'));
        $this->assertTrue($acl->isAllowed(ROLE_ADMINISTRATOR, 'resource_important'));       
    }     
    
    function testSetters() {
        $acl = new SimpleAcl();
        $roles = array(
            ROLE_GUEST => array(ROLE_GUEST), 
            ROLE_IDENTIFIED => array(ROLE_IDENTIFIED)
        );
        $accessList = array(
            'resource1' => array(ROLE_GUEST),
            'resource2' => array(ROLE_GUEST, ROLE_IDENTIFIED)
        );
        
        $acl->setRoles($roles);
        $acl->setAcl($accessList);
        
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertFalse($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource2'));
    }
    
}