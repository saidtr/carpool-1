<?php

include "env.php";
include APP_PATH . "/Bootstrap.php";

$authHelper = AuthHandler::getAuthenticationHelper();
// XXX: Implement!
$interactiveMode = ($authHelper instanceof IAuthenticationHelperInteractive);

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!AuthHandler::isSessionExisting()) {
        // Try to discard bots by dropping requests with no session
        die();
    }

    try {
        if (!$authHelper->validateForm($_POST)) {
            GlobalMessage::setGlobalMessage(_('Failed to authenticate') . ': ' . _('Please fill in all the required details.'), GlobalMessage::ERROR);        
        } else if (AuthHandler::authenticate($authHelper, $_POST) !== false) {
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
    
    // GET after POST
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
	<?php $authHelper->putLogonFormFields(); ?>
	<dd>
		<input type="hidden" id="ref" name="ref" value="<?php echo htmlspecialchars(Utils::getParam('ref', ''))?>" />
		<input type="submit" class="btn primary" value="<?php echo _('Submit')?>" />
	</dd>
	</dl>
</form>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.6.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="js/utils.js"></script>
</body>
</html>
<?php } ?>
