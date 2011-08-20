<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

// Don't need to show any message now
GlobalMessage::clear();

$contact = AuthHandler::getLoggedInUser();

$authUrl = Utils::buildLocalUrl('auth.php', array('c' => $contact['Id'], 'i' => $contact['Identifier'])); 

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="css/common_rtl.css">
<?php endif;?>
<title>Carpool</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar()?>
<?php echo View_Header::render(_("Thanks for joining") . ', ' . htmlspecialchars($contact['Name']) . "!")?>
<div id="content">
<h2><?php echo _('Your contact details')?>:&nbsp;<span style="font-size: x-small"><a href="<?php echo Utils::buildLocalUrl('join.php')?>">(<?php echo _('Edit')?>)</a></span></h2>
<ul id="contactDetails">
<li><span><?php echo _('Name')?>:&nbsp;</span><span><?php echo htmlspecialchars($contact['Name'])?></span></li>
<?php if (!Utils::isEmptyString($contact['Email'])):?>
<li><span><?php echo _('Email')?>:&nbsp;</span><span><?php echo htmlspecialchars(Utils::buildEmail($contact['Email'])) ?></span></li>
<?php endif; ?>
<?php if (!Utils::isEmptyString($contact['Phone'])):?>
<li><span><?php echo _('Phone')?>:&nbsp;</span><span><?php echo htmlspecialchars($contact['Phone'])?></span></li>
<?php endif; ?>
</ul>
<?php if (!empty($contact['Email'])):?>
<p><?php echo _('Confirmation mail was sent to your email.')?></p>
<?php endif;?>
<?php if (AuthHandler::getAuthMode() == AuthHandler::AUTH_MODE_TOKEN): ?>
    <p><?php echo _('You can always update or delete your account by browsing to the following link')?>:</p>
    <p id="authLink"><a href="<?php echo htmlspecialchars($authUrl) ?>"><?php echo htmlspecialchars($authUrl) ?></a></p>
    <p><?php echo _('To use it, just paste the exact link to your browser address bar and hit "Enter".')?></p>
    <p> 
    <?php if (!empty($contact['Email'])):?>
        <?php echo _('Don\'t worry - you got it in the mail.')?>
    <?php else: ?>
        <?php echo _('You should keep this link - this is the only way to modify or delete your account in the future.')?>
    <?php endif; ?>
    </p>
<?php else: ?>
<p><?php printf(_('You can always use "<a href="%s">My Profile</a>" page to update or delete your account any time in the future.'), Utils::buildLocalUrl('join.php'))?></p>
<?php endif; ?>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.6.2.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<?php echo View_Php_To_Js::render();?>
</body>
</html>