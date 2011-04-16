<?php

include "env.php";
include APP_PATH . "/Bootstrap.php";

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!AuthHandler::isSessionExisting()) {
        // Try to discard bots by dropping requests with no session
        die();
    }
    
    extract($_POST, EXTR_SKIP);
    if (!Utils::isEmptyString($user) && !Utils::isEmptyString($password)) {
        $params = array ('user' => $user, 'password' => $password);
        try {
            
            if (AuthHandler::authenticate($params)) {            
                // Redirect to original page
                if (!isset($ref)) {
                    $ref = 'index.php';
                }
                Utils::redirect($ref);
            } else {
                GlobalMessage::setGlobalMessage(_('Failed to authenticate') . ': ' . _('Incorrect credentials.'), GlobalMessage::ERROR);
            }
            
        } catch (Exception $e) {
            logException($e);
            GlobalMessage::setGlobalMessage(_('Failed to authenticate') . ': ' . _('Internal error.'), GlobalMessage::ERROR);
        }
    } else {
        GlobalMessage::setGlobalMessage(_('Please fill in email and password.'), GlobalMessage::ERROR);
    }
    
    Utils::redirect('auth.php');
    
} else {
    
    AuthHandler::putUserToken();
    
?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="css/common_rtl.css">
<?php endif;?>
<title><?php echo _('Authentication')?></title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar()?>
<?php echo View_Header::render(_('Authentication'))?>
<div id="content">
<form id="authenticationForm" action="auth.php" method="post">
	<dl class="noFloat">
	<dd class="mandatory">
		<label for="user"><?php echo _('User name')?></label>
		<input type="text" id="user" name="user" class="textInput" size=20 />
	</dd>
	<dd class="mandatory">
		<label for="password"><?php echo _('Password')?></label>
		<input type="password" id="password" name="password" class="textInput" size=20 />
	</dd>
	<dd>
		<input type="hidden" id="ref" name="ref" value="<?php echo htmlspecialchars(Utils::getParam('ref', ''))?>" />
		<input type="submit" value="<?php echo _('Submit')?>" />
	</dd>
	</dl>
</form>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="js/utils.js"></script>
</body>
</html>
<?php } ?>