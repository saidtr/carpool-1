<?php

/**
 * 
 * Serves as a global database access class
 * 
 * @author itayp
 *
 */
class DatabaseHelper {

    private $_db;
    
    const DATABASE_NAME = 'data';
    
    private static $_instance;
    
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new DatabaseHelper();
		}
		return self::$_instance;
	}

    private function __construct() {
    	$dsn = 'sqlite:' . DATA_PATH . '/' . self::DATABASE_NAME . '.sq3';
    	Logger::info('Connecting to DB: ' . $dsn);
    	try {
            $this->_db = new PDO($dsn);
            if (!$this->_db) {
                Logger::log(Logger::LOG_ERR, 'DB Connection failed: ' . Utils::errorInfoToString($this->_db->errorCode()));    
            }
    	} catch (PDOException $e) {
        	Logger::logException($e);
        }
    }
    
    /**
     * Returns the PDO object holding the DB connection. For testing purpose only 
     * 
     * @return PDO The PDO connection
     */
    public static function getConnection() {
        if (ENV !== ENV_DEVELOPMENT) {
            Logger::err(__METHOD__ . ': This method should not be called!');
            return false;
        }
        
        return self::getInstance()->_db;
    }

    function addCity($name) {
    	Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($name)");
    	try {
	        $stmt = $this->_db->prepare('INSERT INTO Cities(name) VALUES(:name)');
	        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
			
	        $res = $stmt->execute();
	        if (!$res) {
	        	Logger::log(Logger::LOG_ERR, "City insert failed: " . Utils::errorInfoToString($stmt->errorCode()));
	        	return false;
	        }
	        
	        $inserted = $this->_db->lastInsertId();
	        
	        Logger::log(Logger::LOG_INFO, "City $name inserted: $inserted");
	        
	        return $inserted; 
        
    	} catch(PDOException $e) {
    		Logger::logException($e);
    		return false;
    	}
    	
    }

    function getCities() {
        Logger::log(Logger::LOG_DEBUG, __METHOD__);
        try {
        	$rs = $this->_db->query('SELECT id, name FROM cities');
        	if ($rs) {
        		$res = $rs->fetchAll(PDO::FETCH_ASSOC);
        		 
        		// Apply translations
        		foreach ($res as &$record) {
        			$record['Name'] = _($record['Name']);
        		}
        	} else {
        		// Return empty array
        		$res = array();
        	}
            return $res;
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function getAvailableCities($opt) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($opt)");
        if (!$opt === 'Src' && !$opt === 'Dest') {
        	Logger::log(Logger::LOG_ERR, "Illegal access to method " . __METHOD__ . ": $opt");
        	return false;
        }
        try {
            $rs = $this->_db->query('SELECT DISTINCT ' . $opt . 'CityId AS id, Cities.Name AS name FROM Ride, Cities WHERE ' . $opt . 'CityId = Cities.Id');
			
     	    if ($rs) {
                $res = $rs->fetchAll(PDO::FETCH_ASSOC);
        		// Apply translations
        		foreach ($res as &$record) {
        			$record['name'] = _($record['name']);
        		}
            } else {
                // Return empty array
                $res = array();
            }
            return $res;
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function addContact($name, $phone, $email) {
    	Logger::debug(__METHOD__ . "($name, $phone, $email)");
    	
    	try {
    		
    		$identifier = uniqid('', true);
    		
	        $stmt = $this->_db->prepare('INSERT INTO Contacts(name, email, phone, identifier) VALUES (:name, :email, :phone, :identifier)');
	        $stmt->bindParam(':name', $name);
	        $stmt->bindParam(':email', $email);
	        $stmt->bindParam(':phone', $phone);
	        $stmt->bindParam(':identifier', $identifier);
	        
	        if ($stmt->execute()) {
    	        $inserted = $this->_db->lastInsertId();
    	        
    	        Logger::log(Logger::LOG_INFO, "Contact $name inserted: $inserted");
            
    	        return $inserted;
	        } else {
	            Logger::log(Logger::LOG_ERR, "Contact insert failed: " . Utils::errorInfoToString($stmt->errorCode()));
	            return false;
	        }
	        
    	} catch (PDOException $e) {
			Logger::logException($e);   
			return false; 		
    	} 
    }
    
    function updateContact($contactId, $name, $phone, $email) {
    	Logger::debug(__METHOD__ . "($contactId, $name, $phone, $email)");
    	
    	try {  		

	        $stmt = $this->_db->prepare('UPDATE Contacts SET name=:name, email=:email, phone=:phone WHERE id=:contactId');
	        $stmt->bindParam(':name', $name);
	        $stmt->bindParam(':email', $email);
	        $stmt->bindParam(':phone', $phone);
	        $stmt->bindParam(':contactId', $contactId);
	        
	        if ($stmt->execute()) {  	        
    	        Logger::info("Contact number $contactId updated");
    	        return true;
	        } else {
	            Logger::err("Contact $contactId update failed: " . Utils::errorInfoToString($stmt->errorCode()));
	            return false;
	        }
	        
    	} catch (PDOException $e) {
			Logger::logException($e);   
			return false; 		
    	} 
    }
    
    function deleteContact($contactId) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($contactId)");
        try {
            $stmt = $this->_db->prepare('DELETE FROM Contacts WHERE id=:contactId');
            $stmt->bindParam(':contactId', $contactId);
            if ($stmt->execute()) {
                Logger::log(Logger::LOG_INFO, "Contact $contactId successfully deleted");
                return true;
            } else {
                Logger::log(Logger::LOG_ERR, "Contact $contactId could not be deleted: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function addRide(
        $srcCityId, $srcLocation, $destCityId, $destLocation, 
        $timeMorning, $timeEvening, $contactId, $comment, $status) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $status)");

        try {
            $stmt = $this->_db->prepare(
            	'INSERT INTO Ride(srcCityId, srcLocation, destCityId, destLocation, timeMorning, timeEvening, contactId, comment, status) ' . 
            	'VALUES (:srcCityId, :srcLocation, :destCityId, :destLocation, :timeMorning, :timeEvening, :contactId, :comment, :status)');
            $stmt->bindParam(':srcCityId', $srcCityId);
            $stmt->bindParam(':srcLocation', $srcLocation);
            $stmt->bindParam(':destCityId', $destCityId);
            $stmt->bindParam(':destLocation', $destLocation);
            $stmt->bindParam(':timeMorning', $timeMorning);
            $stmt->bindParam(':timeEvening', $timeEvening);
            $stmt->bindParam(':contactId', $contactId);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':status', $status);
            
            if ($stmt->execute()) {
               $inserted = $this->_db->lastInsertId();
               Logger::log(Logger::LOG_INFO, "Ride from $srcCityId to $destCityId with $contactId inserted: $inserted");
               return $inserted; 
            } else {
                Logger::log(Logger::LOG_ERR, "Ride insert failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }

    function updateRide(
        $rideId, $srcCityId, $srcLocation, $destCityId, $destLocation,
        $timeMorning, $timeEvening, $comment, $status) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $status)");

        try {
            $stmt = $this->_db->prepare('UPDATE Ride SET srcCityId=:srcCityId, srcLocation=:srcLocation, destCityId=:destCityId, destLocation=:destLocation, timeMorning=:timeMorning, timeEvening=:timeEvening, comment=:comment, status=:status WHERE id=:rideId');
            $stmt->bindParam(':srcCityId', $srcCityId);
            $stmt->bindParam(':srcLocation', $srcLocation);
            $stmt->bindParam(':destCityId', $destCityId);
            $stmt->bindParam(':destLocation', $destLocation);
            $stmt->bindParam(':timeMorning', $timeMorning);
            $stmt->bindParam(':timeEvening', $timeEvening);           
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':rideId', $rideId);
            
            if ($stmt->execute()) {
               Logger::log(Logger::LOG_INFO, "Ride $rideId successfully updated");
               return true; 
            } else {
                Logger::log(Logger::LOG_ERR, "Ride $rideId update failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function deleteRide($rideId) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($rideId)");
        try {
            $stmt = $this->_db->prepare('DELETE FROM Ride WHERE id=:rideId');
            $stmt->bindParam(':rideId', $rideId);
            if ($stmt->execute()) {
                Logger::log(Logger::LOG_INFO, "Ride $rideId successfully deleted");
                return true;
            } else {
                Logger::log(Logger::LOG_ERR, "Ride $rideId could not be deleted: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function updateRideStatus($rideId, $status) {
        Logger::log(Logger::LOG_DEBUG, __METHOD__ . "($rideId, $status)");
        try {
            if (!in_array($status, array(STATUS_LOOKING, STATUS_OFFERED, STATUS_OFFERED_HIDE))) {
                return false;
            }
            $stmt = $this->_db->prepare('UPDATE Ride SET status=:status WHERE id=:rideId');
            $stmt->bindParam(':rideId', $rideId);
            $stmt->bindParam(':status', $status);
            if ($stmt->execute()) {
                Logger::log(Logger::LOG_INFO, "Status of ride $rideId successfully set to $status");
                return true;
            } else {
                Logger::log(Logger::LOG_ERR, "Ride $rideId update failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
    }
    
    function searchRides($params = null) {
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation, co.Name, co.Email, co.Phone 
        		FROM ride r, contacts co 
        		WHERE co.Id = r.ContactId';
        if (!empty($params)) {
            if (isset($params['status'])) {
                $sql .= ' AND r.Status = ' . $this->_db->quote($params['status']);
            }
            if (isset($params['srcCityId'])) {
                $sql .= ' AND r.SrcCityId = ' . $this->_db->quote($params['srcCityId']);
            }
            if (isset($params['destCityId'])) {
                $sql .= ' AND r.DestCityId = ' . $this->_db->quote($params['destCityId']);
            }
        } 
        // Order - show newer first
        $sql .= ' ORDER BY r.Id DESC';
        Logger::log(Logger::LOG_INFO, __METHOD__ . ": $sql");
        try {
            $rs = $this->_db->query($sql);
            if ($rs) {
                return $rs->fetchAll(PDO::FETCH_ASSOC);
            } else {
                return array();
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }
        
    }
    
    /**
     * 
     * Return ride information for a given contact
     * 
     * @param $contactId int Contact id
     * @return array Ride details
     */
    function getRideByContactId($contactId) {
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation  
                FROM ride r 
                WHERE r.ContactId = :contactId';        
        Logger::debug(__METHOD__ . "($contactId)");
        try {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':contactId', $contactId);
            
        	if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Ride not found - return false
                return false;
            }        
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }    
    }
    
    function getContactByIdentifier($contactId, $identifier) {
        Logger::debug(__METHOD__ . "($contactId, $identifier)");
        try {
            $stmt = $this->_db->query('SELECT Id, Name, Email, Phone FROM contacts WHERE Id=:id AND identifier=:identifier');
			$stmt->bindParam(':id', $contactId);
			$stmt->bindParam(':identifier', $identifier);
            
     	    if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // User not found - return false
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }    	
    }
    
    function getContactById($id) {
        Logger::debug(__METHOD__ . "($id)");
        try {
            $stmt = $this->_db->query('SELECT Id, Name, Email, Phone, Identifier FROM contacts WHERE Id=:id');
			$stmt->bindParam(':id', $id);
            
     	    if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // User not found - return false
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }    	
    }
    
   function getContactByEmail($email) {
        Logger::debug(__METHOD__ . "($email)");
        try {
            try {
            $stmt = $this->_db->query('SELECT * FROM contacts');
            if (!$stmt) {
                Logger::debug('WTF?');
            }
            } catch (Exception $e) {
                Logger::logException($e);
            }
            
			$stmt->bindParam(':email', $email);
			
			if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // User not found - return false
                return false;
            }
        } catch (PDOException $e) {
            Logger::logException($e);
            return false;
        }    	
    }

}
