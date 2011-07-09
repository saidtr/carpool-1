<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

$feedbackOptions = array(
    1 => _("Report a bug"),
    2 => _("Ask a question"),
    3 => _("Request a feature"),
    4 => _("Contact"),
    5 => _("Other")
);

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    if (!AuthHandler::isSessionExisting()) {
        // Try to discard bots by dropping requests with no session
        die();
    }
    
    extract($_POST);
    if (!Utils::isEmptyString($feedback)) {
        $mailHelper = new MailHelper();
        $wantToStr = isset($wantTo) && isset($feedbackOptions[$wantTo]) ? $feedbackOptions[$wantTo] : _("Other");
        
        $params = array(
            'wantTo'   => $wantToStr,
            'feedback' => $feedback,
            'email'    => $email
        );
        $body = $mailHelper->render('views/feedbackMail.php', $params);
        
        $to       = getConfiguration('feedback.mail');
        $toName   = getConfiguration('feedback.to.name');
        $from     = getConfiguration('feedback.from');
        $fromName = getConfiguration('feedback.from.name');
        $replyTo  = Utils::isEmptyString($email) ? null : Utils::buildEmail($email);
        
        Utils::sendMail($to, $toName, $from, 'Carpool feedback', 'New carpool feedback', $body, $replyTo, $replyTo);
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
<?php echo View_Navbar::buildNavbar()?>
<?php echo View_Header::render(_('So, what do you say?'))?>
<div id="content">
<form id="feedbackForm" action="feedback.php" method="post">
	<dl class="noFloat">
	<dd class="mandatory">
		<label for="wantTo"><?php echo _('I want to...')?></label>
		<select id="wantTo" name="wantTo">
		<?php foreach ($feedbackOptions as $key => $feedbackOption): ?>
			<option value="<?php echo $key?>"><?php echo $feedbackOption?></option>
		<?php endforeach; ?>
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
<script type="text/javascript" src="lib/jquery-1.5.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="js/utils.js"></script>
</body>
</html>
<?php } ?>