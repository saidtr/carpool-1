<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!AuthHandler::isSessionExisting()) {
        // Try to discard bots by dropping requests with no session
        die();
    }
    
    extract($_POST);
    if (!Utils::isEmptyString($feedback)) {
        $mail = new View_FeedbackMail();
        $body = $mail->render($wantTo, $feedback, $email);
        
        $to       = getConfiguration('feedback.mail');
        $toName   = getConfiguration('feedback.to.name');
        $from     = getConfiguration('feedback.from');
        $fromName = getConfiguration('feedback.from.name');
        
        Utils::sendMail($to, $toName, $from, 'Carpool feedback', 'New carpool feedback', $body);
        GlobalMessage::setGlobalMessage(_('Thanks for the feedback!'));
    } else {
        GlobalMessage::setGlobalMessage(_('Please write something.'), GlobalMessage::ERROR);
    }
    
    // Get after post
    Utils::redirect('feedback.php');
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
<title>Carpool</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar(AuthHandler::isLoggedIn())?>
<?php echo View_Header::render(_('So, what do you say?'))?>
<div id="content">
<form id="feedbackForm" action="feedback.php" method="post">
	<dl class="noFloat">
	<dd class="mandatory">
		<label for="wantTo"><?php echo _('I want to...')?></label>
		<select id="wantTo" name="wantTo">
			<option value="<?php echo View_FeedbackMail::REPORT_BUG ?>"><?php echo View_FeedbackMail::categoryToString(View_FeedbackMail::REPORT_BUG) ?></option>
			<option value="<?php echo View_FeedbackMail::ASK_QUESTION ?>"><?php echo View_FeedbackMail::categoryToString(View_FeedbackMail::ASK_QUESTION) ?></option>
			<option value="<?php echo View_FeedbackMail::REQUEST_FEATURE ?>"><?php echo View_FeedbackMail::categoryToString(View_FeedbackMail::REQUEST_FEATURE) ?></option>
			<option value="<?php echo View_FeedbackMail::CONTACT ?>"><?php echo View_FeedbackMail::categoryToString(View_FeedbackMail::CONTACT) ?></option>
			<option value="<?php echo View_FeedbackMail::OTHER ?>"><?php echo View_FeedbackMail::categoryToString(View_FeedbackMail::OTHER) ?></option>
		</select>
	</dd>
	<dd class="mandatory">
		<label for="feedback"><?php echo _('Your feedback')?></label>
		<textarea id="feedback" name="feedback" rows=4 cols=30></textarea>
	</dd>
	<dd>
		<label for="email"><?php echo _('Email')?></label>
		<input type="text" id="email" name="email" class="textInput" size=20 />
		<span class="description"><?php echo _('Optional, if you want a response.')?></span>
	</dd>
	<dd>
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