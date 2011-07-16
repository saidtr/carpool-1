<?php

require_once "testenv.php";
require_once "PHPUnit.php";
require_once "Mock_Ldap.php";

class Test_AuthenticationHelperLdap extends PHPUnit_TestCase {

    /**
     * @var AuthenticationHelperLdap
     */
    private $helper;
    
    function contactExists($id) {
        $db = DatabaseHelper::getInstance();
        return ($db->getContactById($id) !== false);
    }

    function contactsCount() {
        $dbCon = DatabaseHelper::getConnection();
        return $dbCon->query('SELECT COUNT(*) AS Cnt FROM Contacts', PDO::FETCH_COLUMN, 0)->fetch();
    }

    function setUp() {
        $this->helper = new AuthenticationHelperLdap();
    }

    function tearDown() {
    }
    
    function testFailedLogon() {
        TestUtils::clearDatabase();
        $params = array('user' => 'User1', 'password' => 'Pass1');
        
        $this->assertEquals(0, $this->contactsCount());
        
        $this->assertFalse($this->helper->authenticate($params));
        
        // Make sure we didn't create any contact
        $this->assertEquals(0, $this->contactsCount());
        
    }
    
    function testSuccessLogonNewUser() {
        TestUtils::clearDatabase();
        $params = array('user' => 'User1', 'password' => 'User1');
        
        $this->assertEquals(0, $this->contactsCount());
        
        $contact = $this->helper->authenticate($params);
        $this->assertTrue(($contact !== false));
                
        // We should've created a contact for this user now
        $this->assertTrue($this->contactExists($contact['Id']));
        $this->assertEquals(1, $this->contactsCount());
    }
    
    /*
     * @depends testSuccessLogonNewUser
     */
    function testSuccessLogonExistingUser() {
        TestUtils::clearDatabase();
        $params = array('user' => 'User2', 'password' => 'User2');
        
        $this->assertEquals(0, $this->contactsCount());
        
        $contact = $this->helper->authenticate($params);
        $this->assertTrue(($contact !== false));    
                
        // A new user should be created
        $this->assertTrue($this->contactExists($contact['Id']));
        $this->assertEquals(1, $this->contactsCount());
        
        // Now, let's authenticate the same user
        $contact2 = $this->helper->authenticate($params);
        $this->assertTrue(($contact['Id'] === $contact2['Id']));
    }
    
}