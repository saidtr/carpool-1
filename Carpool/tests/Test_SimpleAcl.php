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
    
    function testSimpleHierarchy() {
        $acl = new SimpleAcl();
        $acl->addRole(ROLE_GUEST);
        $acl->addRole(ROLE_IDENTIFIED, ROLE_GUEST);
        $acl->addResource(ROLE_GUEST, array('resource1', 'resource2'));
        $acl->addResource(ROLE_IDENTIFIED, array('resource3', 'resource4'));
        
        $acl->dump();
        
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource1'));
        $this->assertTrue($acl->isAllowed(ROLE_GUEST, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource2'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource3'));
        $this->assertTrue($acl->isAllowed(ROLE_IDENTIFIED, 'resource4'));
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource3'));
        $this->assertFalse($acl->isAllowed(ROLE_GUEST, 'resource4'));        
        
    }
    
}