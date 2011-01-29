<?php

class LocaleManager {
	
	const DOMAIN = 'messages';
	
	const DIRECTION_LTR = 0;
	const DIRECTION_RTL = 1;
	
    private static $_instance;
    
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new LocaleManager();
		}
		return self::$_instance;
	}
	
	private $locales;
	private $locale;
	
	private	 function __construct() {
		bindtextdomain(self::DOMAIN, LOCALE_PATH);
		textdomain(self::DOMAIN);
	}
	
	public function getLocales() {
	    return $this->locales;
	}
	
	public function getSelectedLanaguage() {
		return $this->locale['Name'];
	}
	
	public function getSelectedLanaguageId() {
		return $this->locale['Id'];
	}
	
	public function isRtl() {
	    return (isset($this->locale['Direction']) && $this->locale['Direction'] == self::DIRECTION_RTL);
	}

	public function init() {
	    $this->locales = DatabaseHelper::getInstance()->getLocales();
	    
		if (isset($_GET['lang']) && array_key_exists($_GET['lang'], $this->locales)) {
			$this->locale = $this->locales[$_GET['lang']];
			// Set the cookie for 30 days, available to the whole domain
			if (!setcookie('lang', $_GET['lang'], time() + 60 * 60 * 24 * 30, '/')) {
				warn(__METHOD__ . ': Could not set cookie for user! Output already exists.');
			}
			unset($_GET['lang']);
		} else if (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], $this->locales)) {
			$this->locale = $this->locales[$_COOKIE['lang']];
		} else {
			$this->locale = $this->locales[getConfiguration('default.locale')];
		}
		info(__METHOD__ . ' locale selected: ' . $this->locale['Name'] . ' (' . $this->locale['Locale'] . ')');
		setlocale(LC_ALL, $this->locale['Locale']);
		putenv('LC_ALL=' . $this->locale['Locale']);	
	}
	
}