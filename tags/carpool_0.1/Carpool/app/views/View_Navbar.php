<?php

class View_Navbar {
    
	static $pagesGuest = array (
		0 => array('name' => 'Search', 'href' => 'index.php'),
        1 => array('name' => 'Join', 'href' => 'join.php'),
        2 => array('name' => 'Help', 'href' => 'help.php'),
        3 => array('name' => 'Feedback', 'href' => 'feedback.php'),
        4 => array('name' => 'Login', 'href' => 'auth.php')
    );
    
    static $pagesMember = array (
		0 => array('name' => 'Search', 'href' => 'index.php'),
        1 => array('name' => 'My Profile', 'href' => 'join.php'),
        2 => array('name' => 'Help', 'href' => 'help.php'),
        3 => array('name' => 'Feedback', 'href' => 'feedback.php'),
        4 => array('name' => 'Logout', 'href' => 'logout.php')
    );
    
    static function buildLanguageSelector() {
    	$localeManager = LocaleManager::getInstance();
    	$html = '<div id="langHolder"><form id="langSelectorForm" method="get" action="' . $_SERVER['PHP_SELF'] . '"><p>';
    	$html .= '<select id="lang" name="lang">' . PHP_EOL;
    	foreach ($localeManager->getLocales() as $langId => $lang) {
    		$html .= '<option value="' . $langId . '"';
    		if ($langId == $localeManager->getSelectedLanaguageId()) {
    			$html .= ' selected="selected"';
    		}
    		
    		$html .= '>' . _($lang['Name']) . '</option>';
    	}
    	$html .= '</select><input type="submit" class="hidden" /></p></form></div>';
    	return $html;
    }
    
    static function buildRegionSelector() {
        /*
    	$regionManager = RegionManager::getInstance();
    	$html = '<div id="regionHolder"><form id="regionSelectorForm" method="get" action="' . $_SERVER['PHP_SELF'] . '"><p>';
    	$html .= _('Region') . ':&nbsp;<select id="region" name="region">';
    	foreach ($regionManager->getRegions() as $regionId => $region) {
    		$html .= '<option value="' . $regionId . '"';
    		if ($regionId == $regionManager->getCurrentRegionId()) {
    			$html .= ' selected="selected"';
    		}
    		
    		$html .= '>' . _($region['Name']) . '</option>';
    	}
    	$html .= '</select><input type="submit" class="hidden" /></p></form></div>';
    	*/
        $html = '';
    	return $html;
    }

    static function buildNavbar() {
        $html = '';
        
        $role = AuthHandler::getRole();
        $acl = $GLOBALS['acl'];
        $logged = ($role !== ROLE_GUEST);
        
        // Put branding bar if we want one
        if (getConfiguration('branding.enable'))
            $html .= ViewRenderer::renderToString('views/branding.php');
        $html .= '<div id="navbar">';
    	if ($logged) {
    		$pages =& self::$pagesMember;
    		// Put the right ref on the logout link
    		$pages[4]['params'] = array('ref' => Utils::getRunningScript());
    		// If we have no ride yet, the name of join.php is still "Join"
    		if (!AuthHandler::isRideRegistered()) {
    		    $pages[1]['name'] = 'Join';
    		}
    	} else {
    		$pages =& self::$pagesGuest;
    	}
        $str = '<ol>';
        foreach ($pages as $page) {         
            if ($acl->isAllowed($role, $page['href'])) {  
                $str .= '<li><a href="' . Utils::buildLocalUrl($page['href'], isset($page['params']) ? $page['params'] : null) . '" ';
                if ($page['href'] == Utils::getRunningScript()) {
                    $str .= 'class="selected"';
                }
                $str .= '>' . _($page['name']) . '</a></li>';
            }
        }
        $str .= '</ol>';
        $html .= $str;
        $html .= self::buildLanguageSelector();
        $html .= self::buildRegionSelector();
        $html .= '<div class="clearFloat"></div></div>';
        return $html;
    }
	
}