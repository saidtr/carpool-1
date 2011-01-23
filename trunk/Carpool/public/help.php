<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

AuthHandler::putUserToken();

/*
$content = array(
	array(
		'q' => _('What\'s this for?'), 
		'a' => _('This tries to serve as a match-making system for people who wants to share rides together.')
	),
	array(
		'q' => _('Why should I join?'), 
		'a' => _('Because it\'s greener. Because it might save you some money. Because it\'s much more fun.')
	),
	array(
	    'q' => _('How can I delete/modify my account?'),
	    'a' => _('Use the authentication link to authenticate yourself. Then, under "My Profile", you can edit your settings, delete or de-activate your account.')
	),
	array(
	    'q' => _('What is the authentication link? Where can I find it?'),
	    'a' => _('The authentication link used to identify you, instead of username and password. You can find it in the mail you received right after the registration. To use it, just paste this link in your browser.')
	),
	array(
	    'q' => _('I lost the authentication link.'),
	    'a' => _('Use the "Feedback" page to ask for recovery.')
	)

);
*/
$content = DatabaseHelper::getInstance()->getQuestionsAnswers(LocaleManager::getInstance()->getSelectedLanaguageId());

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
<?php echo View_Navbar::buildNavbar(AuthHandler::isLoggedIn())?>
<?php echo View_Header::render(_('Okay, just a quick question.'))?>
<div id="content">
<?php if ($content): ?>
<ul id="questionsHolder">
<?php foreach ($content as $qa): ?>
<li>
	<p class='q'><span><?php echo _('Q')?>:</span><?php echo $qa['Question']?></p>
	<p class='a'><span><?php echo _('A')?>:</span><?php echo $qa['Answer']?></p>
</li>
<?php endforeach; ?>
</ul>
<?php else: ?>
<h2><?php echo _('Sorry, no content found.')?></h2>
<?php endif;?>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="js/utils.js"></script>
</body>
</html>