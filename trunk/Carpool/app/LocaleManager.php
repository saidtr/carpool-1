<?php

class LocaleManager {
	
	const DOMAIN = 'messages';
	
    private static $_instance;
    
	public static function getInstance() {
		if (!isset(self::$_instance)) {
			self::$_instance = new LocaleManager();
		}
		return self::$_instance;
	}
	
	public static $LOCALES = array(
		'en' => array ('name' => 'English', 'locale' => 'en', 'direction' => 'ltr'),
		'he_IL' => array ('name' => 'Hebrew', 'locale' => 'he_IL.UTF-8', 'direction' => 'rtl')
	);
	
	private $locale;
	
	private	 function __construct() {
		bindtextdomain(self::DOMAIN, LOCALE_PATH);
		textdomain(self::DOMAIN);
	}
	
	public function getSelectedLanaguage() {
		return $this->locale['name'];
	}
	
	public function isRtl() {
		return (isset($this->locale['direction']) && $this->locale['direction'] === 'rtl');
	}

	public function init() {
		if (isset($_GET['lang']) && array_key_exists($_GET['lang'], self::$LOCALES)) {
			$this->locale = self::$LOCALES[$_GET['lang']];
			// Set the cookie for 30 days, available to the whole domain
			if (!setcookie('lang', $_GET['lang'], time() + 60 * 60 * 24 * 30, '/')) {
				Logger::warn(__METHOD__ . ': Could not set cookie for user! Output already exists.');
			}
			unset($_GET['lang']);
		} else if (isset($_COOKIE['lang']) && array_key_exists($_COOKIE['lang'], self::$LOCALES)) {
			$this->locale = self::$LOCALES[$_COOKIE['lang']];
		} else {
			$this->locale = self::$LOCALES[getConfiguration('default.locale')];
		}
		Logger::info(__METHOD__ . ' locale selected: ' . $this->locale['name'] . ' (' . $this->locale['locale'] . ')');
		setlocale(LC_ALL, $this->locale['locale']);
		putenv('LC_ALL=' . $this->locale['locale']);	
	}
	
}