<?php

class RegionManager {
		
    private static $_instance;
    
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new RegionManager();
		}
		return self::$_instance;
	}
	
	public static function init() {
	    self::getInstance()->initInternal();
	}
	
	private $_regions;
	private $_currentRegion;
	
	private	 function __construct() {
	}
	
	public static function getDefaultRegion() {
	    return getConfiguration('default.region');
	}
	
	public function getCurrentRegionId() {
	    return $this->_currentRegion['Id'];
	}
	
	public function isMultiRegion() {
		return count($this->_regions) > 1;
	}
	
	public function isValidRegion($regionId) {
		return isset($this->_regions[$regionId]);
	}
	
	public function getRegionConfiguration($regionId = null) {
	    if (is_null($regionId)) {
	        $regionId = $this->_currentRegion['Id'];
	    }
	    
	    return DatabaseHelper::getInstance()->getRegionConfiguration($regionId);
	}
	
	public function setRegion($regionId) {
	    debug(__METHOD__ . "($regionId)");
	    if (!array_key_exists($regionId, $this->_regions)) {
	        err(__METHOD__ . ': Region does not exist');
	        return false;
	    }
	    
	    $this->_currentRegion = $this->_regions[$regionId];
		if (!setcookie('region', $regionId, time() + TWO_WEEKS, getConfiguration('public.path') . '/')) {
			warn(__METHOD__ . ': Could not set cookie for user! Output already exists.');
			return false;
		}
	    return true;
	}
	
	public function getRegions() {
	    return $this->_regions;
	}

	public function initInternal() {
	    $this->_regions = DatabaseHelper::getInstance()->getRegions();
	    
		if (isset($_GET['regionSelector']) && array_key_exists($_GET['regionSelector'], $this->_regions)) {
			$this->_currentRegion = $this->_regions[$_GET['regionSelector']];
			// Set the cookie for 14 days
			if (!setcookie('region', $_GET['regionSelector'], time() + TWO_WEEKS, getConfiguration('public.path') . '/')) {
				warn(__METHOD__ . ': Could not set cookie for user! Output already exists.');
			}
			unset($_GET['region']);
		} else if (isset($_COOKIE['region']) && array_key_exists($_COOKIE['region'], $this->_regions)) {
			$this->_currentRegion = $this->_regions[$_COOKIE['region']];
			// Update cookie expiry time
			setcookie('region', $_COOKIE['region'], time() + TWO_WEEKS, getConfiguration('public.path') . '/');
		} else {
			$this->_currentRegion = $this->_regions[self::getDefaultRegion()];
		}
		info(__METHOD__ . ' region selected: ' . $this->_currentRegion['Id'] . ' (' . $this->_currentRegion['Name'] . ')');
	}
	
}