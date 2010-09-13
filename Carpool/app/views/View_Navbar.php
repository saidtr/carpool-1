<?php

class View_Navbar {
	
	static $pagesGuest = array (
		array('name' => 'Search', 'href' => 'index.php'),
        array('name' => 'Join', 'href' => 'join.php'),
        array('name' => 'Help', 'href' => 'help.php'),
        array('name' => 'Feedback', 'href' => 'feedback.php')
    );
    
    static $pagesMember = array (
		array('name' => 'Search', 'href' => 'index.php'),
        array('name' => 'My Profile', 'href' => 'join.php'),
        array('name' => 'Help', 'href' => 'help.php'),
        array('name' => 'Feedback', 'href' => 'feedback.php'),
        array('name' => 'Logout', 'href' => 'auth.php?action=logout')
    );
    
    static function buildLanguageSelector() {
    	$localeManager = LocaleManager::getInstance();
    	$html = '<div id="langHolder"><form id="langSelectorForm" method="get" action="' . $_SERVER['PHP_SELF'] . '">';
    	$html .= '<select id="lang" name="lang">' . PHP_EOL;
    	foreach (LocaleManager::$LOCALES as $abbr => $lang) {
    		$html .= '<option value="' . $abbr . '"';
    		if ($lang['name'] === $localeManager->getSelectedLanaguage()) {
    			$html .= ' selected="selected"';
    		}
    		
    		$html .= '>' . _($lang['name']) . '</option>';
    	}
    	$html .= '</select><input type="submit" class="hidden" /></form></div>';
    	return $html;
    }

    static function buildNavbar($logged = false) {
        $html = '<div id="navbar">';
    	if ($logged) {
    		$pages =& self::$pagesMember;
    	} else {
    		$pages =& self::$pagesGuest;
    	}
        $str = '<ol>';
        foreach ($pages as $page) {           
            $str .= '<li><a href="' . Utils::buildLocalUrl($page['href']) . '" ';
            if ($page['href'] == Utils::getRunningScript()) {
                $str .= 'class="selected"';
            }
            $str .= '>' . _($page['name']) . '</a></li>';
        }
        $str .= '</ol>';
        $html .= $str;
        $html .= self::buildLanguageSelector();
        $html .= '<div class="clearFloat"></div></div>';
        return $html;
    }
	
}