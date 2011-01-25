<?php

include "env.php";
include APP_PATH . "/Bootstrap.php";

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    Utils::redirect('cms.php');
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
<title>Carpool CMS</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar(AuthHandler::isLoggedIn())?>
<?php echo View_Header::render(_('Carpool CMS'))?>
<div id="content">
<form id="qaDataForm" action="cms.php" method="post">
<?php 

$currentQuestions = DatabaseHelper::getInstance()->getQuestionsAnswers(LocaleManager::getInstance()->getSelectedLanaguageId());

foreach($currentQuestions as $questionAnswer):
?>
	<dl>
	<dd>
		<label for="question"><?php echo _('Question')?></label>
		<input type="text" id="question_<?php echo $questionAnswer['Id'] ?>" value="<?php echo $questionAnswer['Question'] ?>" />
	</dd>
	<dd>
		<label for="answer"><?php echo _('Answer')?></label>
		<input type="text" id="answer_<?php echo $questionAnswer['Id'] ?>" value="<?php echo $questionAnswer['Answer'] ?>" />
	</dd>
	</dl>
<?php endforeach; ?>
	<div class="clearFloat"></div>	
	<dl class="noFloat">
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