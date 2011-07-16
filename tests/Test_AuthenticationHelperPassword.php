<?php

require_once "testenv.php";
require_once "PHPUnit.php";

class Test_AuthenticationHelperPassword extends PHPUnit_TestCase {

    /**
     * @var AuthenticationHelperPassword
     */
    private $helper;

    function setUp() {
        $this->helper = new AuthenticationHelperPassword();
    }

    function tearDown() {
    }
    
    function testFailedLogon() {
        TestUtils::clearDatabase(); 
        DatabaseHelper::getInstance()->addContact('user1', '', 'user1@mail.com', ROLE_IDENTIFIED, Utils::hashPassword('pass1'));
        
        // User exists, but password is wrong
        $params1 = array('email' => 'user1@mail.com', 'password' => 'PASS1');
        $this->assertFalse($this->helper->authenticate($params1));
        
        // Empty password
        $params2 = array('email' => 'user1@mail.com', 'password' => '');
        $this->assertFalse($this->helper->authenticate($params2));
        
        // No such user
        $params3 = array('email' => 'nosuch@mail.com', 'password' => 'XXXX');
        $this->assertFalse($this->helper->authenticate($params3));
        
        // Empty user
        $params4 = array('email' => '', 'password' => 'XXXX');
        $this->assertFalse($this->helper->authenticate($params4));
    }
    
    function testSuccessLogonNewUser() {
        TestUtils::clearDatabase(); 
        $id = DatabaseHelper::getInstance()->addContact('user2', '', 'user2@mail.com', ROLE_IDENTIFIED, Utils::hashPassword('---longpassword123---'));
        
        // First let's fail
        $params1 = array('email' => 'user2@mail.com', 'password' => '---longpassword12---');
        $this->assertFalse($this->helper->authenticate($params1));
        
        // This should work
        $params2 = array('email' => 'user2@mail.com', 'password' => '---longpassword123---');
        $contact = $this->helper->authenticate($params2);
        $this->assertTrue($contact !== false);        
        $this->assertEquals($id, $contact['Id']);
        $this->assertEquals(ROLE_IDENTIFIED, $contact['Role']);
    }
    
}