<?php

require_once "testenv.php";
require_once "../app/QueryBuilder.php";

class Test_QueryBuilder extends PHPUnit_Framework_TestCase {

    public function testQueryInsertSqlStringMultipleColumns() {
        $query = new QueryInsert('MyTable');
        $query->setColumns(array('col1', 'col2'));
        $requiredSql = 'INSERT INTO MyTable(col1,col2) VALUES(?,?)';

        $this->assertEquals($query->__toString(), $requiredSql);
    }

    public function testQueryInsertSqlStringSingleColumn() {
        $query = new QueryInsert('MyTable');
        $query->setColumns(array('col'));
        $requiredSql = 'INSERT INTO MyTable(col) VALUES(?)';

        $this->assertEquals($query->__toString(), $requiredSql);
    }

    public function testQueryUpdateSqlStringSingleColumn() {
        $query = new QueryUpdate('MyTable');
        $query->setColumns(array('col'));
        $requiredSql = 'UPDATE MyTable SET col=?';

        $this->assertEquals($query->__toString(), $requiredSql);
    }

    public function testQueryUpdateSqlStringMultipleColumns() {
        $query = new QueryUpdate('MyTable');
        $query->setColumns(array('col1', 'col2'));
        $requiredSql = 'UPDATE MyTable SET col1=?,col2=?';

        $this->assertEquals($query->__toString(), $requiredSql);
    }
    
    public function testQueryUpdateSqlStringWithCondition() {
        $query = new QueryUpdate('MyTable');
        $query->setColumns(array('col1', 'col2'));
        $query->setCondition('col3 > 0');
        $requiredSql = 'UPDATE MyTable SET col1=?,col2=? WHERE col3 > 0';

        $this->assertEquals($query->__toString(), $requiredSql);
    }

    public function testInsert() {
        TestUtils::clearDatabase();
        DatabaseHelper::getInstance()->insert(
        	'Contacts', 
            array(
                	'Email' => 'test1@email.com',
                    'Name' => 'test1',
                    'Phone' => '123',
                    'Role' => ROLE_GUEST
            ));
            
        $contact = DatabaseHelper::getInstance()->getContactByEmail('test1@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('test1', $contact['Name']);
        $this->assertEquals('123', $contact['Phone']);
        $this->assertEquals(ROLE_GUEST, $contact['Role']);
    }

    public function testUpdate1() {
        TestUtils::clearDatabase();
        DatabaseHelper::getInstance()->insert(
        	'Contacts', 
            array(
                	'Email' => 'test1@email.com',
                    'Phone' => '123',
            		'Name' => 'test1',
                    'Role' => ROLE_GUEST
            ));
            
         DatabaseHelper::getInstance()->update(
        	'Contacts', 
            array(
            		'Name' => 'test2'
            ));

        $contact = DatabaseHelper::getInstance()->getContactByEmail('test1@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('test2', $contact['Name']);
        // Now make sure rest of the fields are still the same
        $this->assertEquals('123', $contact['Phone']);
        $this->assertEquals(ROLE_GUEST, $contact['Role']);
    }
    
    public function testUpdate2() {
        TestUtils::clearDatabase();
        DatabaseHelper::getInstance()->insert(
        	'Contacts', 
            array(
                	'Email' => 'test1@email.com',
                    'Phone' => '123',
            		'Name' => 'test1',
                    'Role' => ROLE_GUEST
            ));
            
        DatabaseHelper::getInstance()->insert(
        	'Contacts', 
            array(
                	'Email' => 'test2@email.com',
                    'Phone' => '456',
            		'Name' => 'test2',
                    'Role' => ROLE_GUEST
            ));
            
         // No condition - update all phone entries
         DatabaseHelper::getInstance()->update(
        	'Contacts', 
            array(
            		'Phone' => '987'
            ));
            
         DatabaseHelper::getInstance()->update(
        	'Contacts', 
            array(
            		'Name' => 'change1'
            ),
            'Email = ?',
            array('test1@email.com')
            );

        $contact = DatabaseHelper::getInstance()->getContactByEmail('test1@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('change1', $contact['Name']);
        // Now make sure rest of the fields are still the same
        $this->assertEquals('987', $contact['Phone']);
        $this->assertEquals(ROLE_GUEST, $contact['Role']);
        
        // The other contact should still be the same
        $contact = DatabaseHelper::getInstance()->getContactByEmail('test2@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('test2', $contact['Name']);
        $this->assertEquals('987', $contact['Phone']);
        $this->assertEquals(ROLE_GUEST, $contact['Role']);
    }

    public function testUpdate3() {
        TestUtils::clearDatabase();
        DatabaseHelper::getInstance()->insert(
        	'Contacts', 
            array(
                	'Email' => 'test1@email.com',
                    'Phone' => '123',
            		'Name' => 'test1',
                    'Role' => ROLE_GUEST
            ));
            
        $contact = DatabaseHelper::getInstance()->getContactByEmail('test1@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('test1', $contact['Name']);
        $updatedData = array(
            'Phone' => '987',
            'Name'  => null,
            'Role'  => ROLE_ADMINISTRATOR,
            'Email' => null
        );
        DatabaseHelper::getInstance()->update('Contacts', $updatedData, 'id=?', array($contact['Id']), true);
  
        // Make sure only the relevant fields were changed
        $contact = DatabaseHelper::getInstance()->getContactByEmail('test1@email.com');
        $this->assertTrue($contact !== false);
        $this->assertEquals('test1', $contact['Name']);
        $this->assertEquals('987', $contact['Phone']);
        $this->assertEquals(ROLE_ADMINISTRATOR, $contact['Role']);
    }
    

}