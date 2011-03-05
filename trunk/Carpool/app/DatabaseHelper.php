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
    	$dsn = str_replace('%DATAPATH%', DATA_PATH, getConfiguration('database.dsn'));
    	$user = getConfiguration('database.user');
    	$pass = getConfiguration('database.pass');
    	
    	info('Connecting to DB: ' . $dsn);
    	try {
            $this->_db = new PDO($dsn, $user, $pass);
            // Use exceptions as error handling
            $this->_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            if (!$this->_db) {
                err('DB Connection failed: ' . Utils::errorInfoToString($this->_db->errorCode()));    
            }
            if (($initCode = getConfiguration('database.init')) !== false)
            {
                $this->_db->query($initCode);
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
        	$rs = $this->_db->query('SELECT Id, Name FROM Cities');
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
	        
	        $stmt->execute();
    	    $inserted = $this->_db->lastInsertId();
    	        
    	    info("Contact $name inserted: $inserted");
            
    	    return $inserted;
    	} catch (PDOException $e) {
			logException($e);   
			throw $e;
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
	        
	        $stmt->execute();  	        
    	    info("Contact number $contactId updated");
	        
    	} catch (PDOException $e) {
			logException($e);   
			throw $e;
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
        $timeMorning, $timeEvening, $contactId, $comment, $status, $notify) {
        debug(__METHOD__ . "($srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $contactId, $comment, $status, $notify)");

        try {
            $stmt = $this->_db->prepare(
            	'INSERT INTO Ride(srcCityId, srcLocation, destCityId, destLocation, timeMorning, timeEvening, contactId, comment, status, timeCreated, timeUpdated, Active, Notify) ' . 
            	'VALUES (:srcCityId, :srcLocation, :destCityId, :destLocation, :timeMorning, :timeEvening, :contactId, :comment, :status, :timeCreated, :timeUpdated, :active, :notify)');
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
            $active = RIDE_ACTIVE;
            $stmt->bindParam(':active', $active);
            $stmt->bindParam(':notify', $notify);
            
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
        $timeMorning, $timeEvening, $comment, $status, $notify) {
        debug(__METHOD__ . "($rideId, $srcCityId, $srcLocation, $destCityId, $destLocation, $timeMorning, $timeEvening, $comment, $status, $notify)");

        try {
            $stmt = $this->_db->prepare('UPDATE Ride SET srcCityId=:srcCityId, srcLocation=:srcLocation, destCityId=:destCityId, destLocation=:destLocation, timeMorning=:timeMorning, timeEvening=:timeEvening, comment=:comment, status=:status, timeUpdated=:timeUpdated, notify=:notify WHERE id=:rideId');
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
            $stmt->bindParam(':notify', $notify);
            
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
    
    function updateRideActive($rideId, $active) {
        debug(__METHOD__ . "($rideId, $active)");
        try {
            if (!in_array($active, array(RIDE_ACTIVE, RIDE_INACTIVE))) {
                return false;
            }
            $stmt = $this->_db->prepare('UPDATE Ride SET Active=:active, timeUpdated=:timeUpdated WHERE id=:rideId');
            $stmt->bindParam(':rideId', $rideId);
            $stmt->bindParam(':active', $active);
            $curTime = time();
            $stmt->bindParam(':timeUpdated', $curTime);
            if ($stmt->execute()) {
                info("Activity of ride $rideId successfully set to $active");
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
				'WHERE co.Id = r.ContactId AND r.Active = ' . RIDE_ACTIVE; 
        if (!empty($params)) {
            if (isset($params['status'])) {
                $sql .= ' AND ' . $this->conditionEqualsOrIn('r.Status', $params['status']);
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
            if (isset ($params['notify'])) {
                $sql .= ' AND r.notify = ' . $this->_db->quote($params['notify']);
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
        $sql = 'SELECT r.Id, r.Comment, r.Status, r.TimeEvening, r.TimeMorning, r.DestCityId, r.DestLocation, r.SrcCityId, r.SrcLocation, r.Active, r.Notify   
                FROM ride r 
                WHERE r.ContactId = :contactId LIMIT 1';        
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
            $stmt = $this->_db->prepare('SELECT Id, Name, Email, Phone, Identifier FROM contacts WHERE Id=:id');
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
            $stmt = $this->_db->prepare('SELECT Id, Name, Phone FROM contacts WHERE Email=:email');
            
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
            $stmt = $this->_db->prepare('UPDATE ShowInterestNotifier SET LastRun=:time');
			$stmt->bindParam(':time', $time);
			$stmt->execute();
			return true;
        } catch (PDOException $e) {
            logException($e);
            return false;
        }    	   	
    }
    
    function getLocales() {
        debug(__METHOD__ . "()");
        
        try {
            $stmt = $this->_db->query('SELECT Id, Name, Abbrev, Locale, Direction FROM Languages');
            $res = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $locales = array();
            foreach ($res as $locale) {
                $locales[$locale['Id']] = $locale;
            }
            return $locales;
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    function getQuestionsAnswersByLang($lang) {
        debug(__METHOD__ . "($lang)");
        
        assert(array_key_exists($lang, LocaleManager::getInstance()->getLocales()));
        try {
            $stmt = $this->_db->prepare('SELECT Id, Question, Answer FROM QuestionsAnswers WHERE Lang = :lang');
            $stmt->bindParam(':lang', $lang);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }
    
    function getQuestionsAnswers() {
        debug(__METHOD__ . "()");
               
        try {
            $rs = $this->_db->query('SELECT Lang, Id, Question, Answer FROM QuestionsAnswers ORDER BY Id, Lang');
            $all = $rs->fetchAll(PDO::FETCH_ASSOC);
            $res = array();
            foreach ($all as $qa) {
                $res[$qa['Id']][$qa['Lang']] = array('Id' => $qa['Id'], 'Question' => $qa['Question'], 'Answer' => $qa['Answer']);
            }
            return $res;
        } catch (PDOException $e) {
            logException($e);
            return false;
        }
    }    
    
    function updateQuestionAnswer($id, $langId, $question, $answer) {
        debug(__METHOD__ . "($id, $langId, $question, $answer)");
        
        try {
            $setQuestion = !is_null($question);
            $setAnswer = !is_null($answer);
            
            // Assert we try to change at least one
            assert($setQuestion || $setAnswer);
            
            $sql = 'UPDATE QuestionsAnswers SET ';
            if ($setQuestion)
                $sql .= 'Question=:question';
            if ($setAnswer) {
                if ($setQuestion)
                    $sql .= ', ';
                $sql .= 'Answer=:answer';
            }
            
            $sql .= ' WHERE Id=:id AND Lang=:lang';
            
            $stmt = $this->_db->prepare($sql);
            if ($setQuestion) {
                $stmt->bindParam(':question', $question);
            }
            if ($setAnswer) {
                $stmt->bindParam(':answer', $answer);
            }
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->bindParam(':lang', $langId, PDO::PARAM_INT);
            return $stmt->execute();
        } catch(PDOException $e) {
            logException($e);
            return false;            
        }               
    }
    
    /**
     * 
     * Get the next available ID for use with new QAs. 
     * 
     */
    function getNextQuestionAnswerId() {
        debug(__METHOD__ . "()");
        
        try {
            
            // We might have a synchronization
            // problem here, but typcial usage pattern doesn't worth the work of creating a 
            // better auto-incrementing mechanism.
            $rs = $this->_db->query('SELECT MAX(Id) AS Id FROM QuestionsAnswers');
            $res = $rs->fetch(PDO::FETCH_ASSOC);
            if ($res) {
                return ($res['Id'] + 1);
            }
            // Nothing found - starting from 1
            return 1;
        } catch(PDOException $e) {
            logException($e);
            return false;            
        }   
    }
    
    function insertQuestionAnswer($id, $langId, $question, $answer) {
        debug(__METHOD__ . "($id, $langId, $question, $answer)");
        
        try {
            $stmt = $this->_db->prepare('INSERT INTO QuestionsAnswers (Id, Lang, Question, Answer) VALUES (:id, :lang, :question, :answer)');
            $stmt->bindParam(':question', $question);
            $stmt->bindParam(':answer', $answer);
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':lang', $langId);
            return $stmt->execute();
        } catch(PDOException $e) {
            logException($e);
            return false;            
        }               
    }
    
    function conditionEqualsOrIn($field, $val) {
        $condStr = '';
        if (is_array($val)) {           
            if (empty($val)) {
                // Nothing fits - just give impossible condition
                $condStr .= '1 = 0';
            } else {
                $condStr .= $field . ' IN (';
                foreach ($val as $v) {
                    $condStr .= $this->_db->quote($v);    
                    $condStr .= ', ';
                }
                $condStr = substr($condStr, 0, -2);
                $condStr .= ')';
            }
        } else {
            $condStr .= $field . ' = ' . $this->_db->quote($val);
        }
        return $condStr;
    }
    

}
