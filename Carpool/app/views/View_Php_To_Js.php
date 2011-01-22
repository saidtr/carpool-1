<?php 

/**
 * 
 * Generic class for putting PHP data as JS variables 
 * 
 * @author Itay
 *
 */
class View_Php_To_Js {

    static $CONSTANTS = array (
        'xhr' => array(
            'ADD_RIDE'        => 'xhr/AddRideAll.php',
    		'SHOW_INTEREST'   => 'xhr/ShowInterest.php',
            'DEL_RIDE'        => 'xhr/DeleteRide.php',
    		'GET_CITIES'      => 'xhr/GetCities.php',
    		'SEARCH_RIDES'    => 'xhr/SearchRides.php',
            'TOGGLE_ACTIVATE' => 'xhr/ActivateToggle.php'
    		),
        'LOCATION_NOT_FOUND' => LOCATION_NOT_FOUND,
        'LOCATION_DONT_CARE' => LOCATION_DONT_CARE,
    	'STATUS_DONT_CARE'   => STATUS_DONT_CARE,
    	'STATUS_LOOKING'     => STATUS_LOOKING,
    	'STATUS_OFFERED'     => STATUS_OFFERED,
        'TIME_IRRELAVANT' => TIME_IRRELEVANT,
        'TIME_DIFFER' => TIME_DIFFERS
    );
    
    static $vars = array();
    
    public static function putTranslations($strings) {
    	$translations = array();
    	foreach ($strings as $str) {
    		$translations[$str] = _($str);
    	}
    	self::putVariable('Translations', $translations);
    }
    
    public static function putVariable($name, $value) {
        self::$vars[$name] = $value;
    }
    
    public static function putConstant($name, $value) {
        self::$CONSTANTS[$name] = $value;
    }
    
    public static function render() {
        self::putVariable('Constants', self::$CONSTANTS);
        $html = '<script type="text/javascript">' . PHP_EOL;
        foreach (self::$vars as $name => $value) {
            $html .= 'var ' . htmlspecialchars($name) . '=' . json_encode($value) . ';' . PHP_EOL;
        }
        $html .= '</script>' . PHP_EOL;
        
        return $html;
    }
 		
}
