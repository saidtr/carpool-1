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
    	info('Connecting to DB: ' . $dsn);
    	try {
            $this->_db = new PDO($dsn);
            // Use exceptions as error handling
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (!$this->_db) {
                err('DB Connection failed: ' . Utils::errorInfoToString($this->_db->errorCode()));    
            }
    	} catch (PDOException $e) {
        	logException($e);
        }
    }
    
    /**
     * Returns the PDO object holding the DB connection. For testing purpose only 
     * 
     * @return PDO The PDO connection
     */
    public static function getConnection() {
        if (ENV !== ENV_DEVELOPMENT) {
            err(__METHOD__ . ': This method should not be called!');
            return false;
        }
        
        return self::getInstance()->_db;
    }
    
    public function beginTransaction() {
        debug(__METHOD__);
        return $this->_db->beginTransaction();
    }
    
    public function rollBack() {
        debug(__METHOD__);
        return $this->_db->rollBack();
    }
    
    public function commit() {
        debug(__METHOD__);
        return $this->_db->commit();
    }
    
    function addCity($name) {
    	debug(__METHOD__ . "($name)");
    	try {
	        $stmt = $this->_db->prepare('INSERT INTO Cities(name) VALUES(:name)');
	        $stmt->bindParam(':name', $name, PDO::PARAM_STR);
			
	        $res = $stmt->execute();
	        if (!$res) {
	        	err("City insert failed: " . Utils::errorInfoToString($stmt->errorCode()));
	        	return false;
	        }
	        
	        $inserted = $this->_db->lastInsertId();
	        
	        info("City $name inserted: $inserted");
	        
	        return $inserted; 
        
    	} catch(PDOException $e) {
    		logException($e);
    		return false;
    	}
    	
    }

    function getCities() {
        debug(__METHOD__);
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
            logException($e);
            return false;
        }
    }
    
    function getAvailableCities($opt) {
        debug(__METHOD__ . "($opt)");
        if (!$opt === 'Src' && !$opt === 'Dest') {
        	err("Illegal access to method " . __METHOD__ . ": $opt");
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
            logException($e);
            return false;
        }
    }
    
    function addContact($name, $phone, $email) {
    	debug(__METHOD__ . "($name, $phone, $email)");
    	
    	try {
    		
    		$identifier = uniqid('', true);
    		
	        $stmt = $this->_db->prepare('INSERT INTO Contacts(name, email, phone, identifier) VALUES (:name, :email, :phone, :identifier)');
	        $stmt->bindParam(':name', $name);
	        $stmt->bindParam(':email', $email);
	        $stmt->bindParam(':phone', $phone);
	        $stmt->bindParam(':identifier', $identifier);
	        
	        if ($stmt->execute()) {
    	        $inserted = $this->_db->lastInsertId();
    	        
    	        info("Contact $name inserted: $inserted");
            
    	        return $inserted;
	        } else {
	            err("Contact insert failed: " . Utils::errorInfoToString($stmt->errorCode()));
	            return false;
	        }
	        
    	} catch (PDOException $e) {
			logException($e);   
			return false; 		
    	} 
    }
    
    function updateContact($contactId, $name, $phone, $email) {
    	debug(__METHOD__ . "($contactId, $name, $phone, $email)");
    	
    	try {  		

	        $stmt = $this->_db->prepare('UPDATE Contacts SET name=:name, email=:email, phone=:phone WHERE id=:contactId');
	        $stmt->bindParam(':name', $name);
	        $stmt->bindParam(':email', $email);
	        $stmt->bindParam(':phone', $phone);
	        $stmt->bindParam(':contactId', $contactId);
	        
	        if ($stmt->execute()) {  	        
    	        info("Contact number $contactId updated");
    	        return true;
	        } else {
	            err("Contact $contactId update failed: " . Utils::errorInfoToString($stmt->errorCode()));
	            return false;
	        }
	        
    	} catch (PDOException $e) {
			logException($e);   
			return false; 		
    	} 
    }
    
    function deleteContact($contactId) {
        debug(__METHOD__ . "($contactId)");
        try {
            $stmt = $this->_db->prepare('DELETE FROM Contacts WHERE id=:contactId');
            $stmt->bindParam(':contactId', $contactId);
            if ($stmt->execute()) {
                info("Contact $contactId successfully deleted");
                return true;
            } else {
                err("Contact $contactId could not be deleted: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    function addRide(
        $srcCityId, $srcLocation, $destCityId, $destLocation, 
        $timeMorning, $timeEvening, $contactId, $comment, $status) {
        debug(__METHOD__ . "($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $status)");

        try {
            $stmt = $this->_db->prepare(
            	'INSERT INTO Ride(srcCityId, srcLocation, destCityId, destLocation, timeMorning, timeEvening, contactId, comment, status, timeCreated, timeUpdated) ' . 
            	'VALUES (:srcCityId, :srcLocation, :destCityId, :destLocation, :timeMorning, :timeEvening, :contactId, :comment, :status, :timeCreated, :timeUpdated)');
            $stmt->bindParam(':srcCityId', $srcCityId);
            $stmt->bindParam(':srcLocation', $srcLocation);
            $stmt->bindParam(':destCityId', $destCityId);
            $stmt->bindParam(':destLocation', $destLocation);
            $stmt->bindParam(':timeMorning', $timeMorning);
            $stmt->bindParam(':timeEvening', $timeEvening);
            $stmt->bindParam(':contactId', $contactId);
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':status', $status);
            $curTime = time();
            $stmt->bindParam(':timeCreated', $curTime);
            $stmt->bindParam(':timeUpdated', $curTime);
            
            if ($stmt->execute()) {
               $inserted = $this->_db->lastInsertId();
               info("Ride from $srcCityId to $destCityId with $contactId inserted: $inserted");
               return $inserted; 
            } else {
                err("Ride insert failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }

    function updateRide(
        $rideId, $srcCityId, $srcLocation, $destCityId, $destLocation,
        $timeMorning, $timeEvening, $comment, $status) {
        debug(__METHOD__ . "($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $status)");

        try {
            $stmt = $this->_db->prepare('UPDATE Ride SET srcCityId=:srcCityId, srcLocation=:srcLocation, destCityId=:destCityId, destLocation=:destLocation, timeMorning=:timeMorning, timeEvening=:timeEvening, comment=:comment, status=:status, timeUpdated=:timeUpdated WHERE id=:rideId');
            $stmt->bindParam(':srcCityId', $srcCityId);
            $stmt->bindParam(':srcLocation', $srcLocation);
            $stmt->bindParam(':destCityId', $destCityId);
            $stmt->bindParam(':destLocation', $destLocation);
            $stmt->bindParam(':timeMorning', $timeMorning);
            $stmt->bindParam(':timeEvening', $timeEvening);           
            $stmt->bindParam(':comment', $comment);
            $stmt->bindParam(':status', $status);
            $curTime = time();
            $stmt->bindParam(':timeUpdated', $curTime);
            $stmt->bindParam(':rideId', $rideId);
            
            if ($stmt->execute()) {
               info("Ride $rideId successfully updated");
               return true; 
            } else {
                err("Ride $rideId update failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    function deleteRide($rideId) {
        debug(__METHOD__ . "($rideId)");
        try {
            $stmt = $this->_db->prepare('DELETE FROM Ride WHERE id=:rideId');
            $stmt->bindParam(':rideId', $rideId);
            if ($stmt->execute()) {
                info("Ride $rideId successfully deleted");
                return true;
            } else {
                err("Ride $rideId could not be deleted: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
   function deleteRideByContact($contactId) {
        debug(__METHOD__ . "($contactId)");
        try {
            $stmt = $this->_db->prepare('DELETE FROM Ride WHERE ContactId=:contactId');
            $stmt->bindParam(':contactId', $contactId);
            if ($stmt->execute()) {
                info("Rides for $contactId successfully deleted");
                return true;
            } else {
                err("Rides for $contactId could not be deleted: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    function updateRideStatus($rideId, $status) {
        debug(__METHOD__ . "($rideId, $status)");
        try {
            if (!in_array($status, array(STATUS_LOOKING, STATUS_OFFERED, STATUS_OFFERED_HIDE))) {
                return false;
            }
            $stmt = $this->_db->prepare('UPDATE Ride SET status=:status, timeUpdated=:timeUpdated WHERE id=:rideId');
            $stmt->bindParam(':rideId', $rideId);
            $stmt->bindParam(':status', $status);
            $curTime = time();
            $stmt->bindParam(':timeUpdated', $curTime);
            if ($stmt->execute()) {
                info("Status of ride $rideId successfully set to $status");
                return true;
            } else {
                err("Ride $rideId update failed: " . Utils::errorInfoToString($stmt->errorInfo()));
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    /**
     * Search all available rides, possibly with various parameters
     * 
     * @param $params array Array that holds various parameters in key-value format
     * @return mixed Array with the search results, or false in case of failures
     */
    function searchRides($params = null) {
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation, r.ContactId, co.Name, co.Email, co.Phone ' .         		
				'FROM ride r, contacts co ' .         		
				'WHERE co.Id = r.ContactId'; 
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
            if (isset($params['minTimeCreated'])) {
            	$sql .= ' AND r.timeCreated >= ' . $this->_db->quote($params['minTimeCreated']);
            }
        } 
        // Order - show newer first
        $sql .= ' ORDER BY r.Id DESC';
        info(__METHOD__ . ": $sql");
        try {
            $rs = $this->_db->query($sql);
            if ($rs) {
            	$res = $rs->fetchAll(PDO::FETCH_ASSOC);
            	
            	// Now, take care of the cities
            	$cities = $this->getCities();
            	$citiesMapper = array();
            	foreach ($cities as $city) {
            		$citiesMapper[$city['Id']] = $city['Name'];
            	}
            	$citiesMapper[LOCATION_NOT_FOUND] = _('N/A');
            	$citiesMapper[LOCATION_DONT_CARE] = _('Everywhere');

            	// Apply translations
            	foreach ($res as &$record) {
            		$record['SrcCity'] = _($citiesMapper[$record['SrcCityId']]);
        			$record['DestCity'] = _($citiesMapper[$record['DestCityId']]);
        		}
        		
        		return $res;
            } else {
                return array();
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
        
    }
    
    /**
     * 
     * Return provided ride information for a given contact.
     * This function return a single result. It is assumed that
     * no more a single ride can be provided by each contact
     * 
     * @param $contactId int Contact id
     * @return array Ride details (a single result)
     * 
     */
    function getRideProvidedByContactId($contactId) {
        debug(__METHOD__ . "($contactId)");
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation  
                FROM ride r 
                WHERE r.Status IN (' . STATUS_OFFERED . ', ' . STATUS_OFFERED_HIDE . ') AND r.ContactId = :contactId LIMIT 1';        
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
            logException($e);
            return false;
        }    
    }
    
    /**
     * 
     * Return ride information identified by id
     * 
     * @param $rideId int Ride id
     * @return array Ride details
     * 
     */
    function getRideById($rideId) {
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation, co.Name, co.Email, co.Phone 
        		FROM ride r, contacts co 
        		WHERE co.Id = r.ContactId AND r.Id = :rideId';        
        debug(__METHOD__ . "($rideId)");
        try {
            $stmt = $this->_db->prepare($sql);
            $stmt->bindParam(':rideId', $rideId);
            
        	if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // Ride not found - return false
                return false;
            }        
        } catch (PDOException $e) {
            logException($e);
            return false;
        }    
    }
    
    function getContactByIdentifier($contactId, $identifier) {
        debug(__METHOD__ . "($contactId, $identifier)");
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
            logException($e);
            return false;
        }    	
    }
    
    function getContactById($id) {
        debug(__METHOD__ . "($id)");
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
            logException($e);
            return false;
        }    	
    }
    
   function getContactByEmail($email) {
        debug(__METHOD__ . "($email)");
        try {
            $stmt = $this->_db->query('SELECT Id, Name, Phone FROM contacts WHERE Email=:email');
            
			$stmt->bindParam(':email', $email);
			
			if ($stmt->execute()) {
                return $stmt->fetch(PDO::FETCH_ASSOC);
            } else {
                // User not found - return false
                return false;
            }
        } catch (PDOException $e) {
            logException($e);
            return false;
        }    	
    }
    
    function getLastShowInterestNotifier() {
         debug(__METHOD__ . "()");
         try {
         	$rs = $this->_db->query('SELECT LastRun FROM ShowInterestNotifier');
            $res = $rs->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                return $res['LastRun'];
            } else {
                return false;
            }        		 
        } catch (PDOException $e) {
            logException($e);
            return false;
        }    	   	
    }
    
    function updateLastShowInterestNotifier($time) {
         debug(__METHOD__ . "($time)");
         assert(is_integer($time) === true && $time > 0);
         try {
            $stmt = $this->_db->query('UPDATE ShowInterestNotifier SET LastRun=:time');
			$stmt->bindParam(':time', $time);
			$stmt->execute();
			return true;
        } catch (PDOException $e) {
            logException($e);
            return false;
        }    	   	
    }

}
