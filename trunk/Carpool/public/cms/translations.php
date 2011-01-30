<?php

include "../env.php";
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
<link rel="stylesheet" type="text/css" href="../css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="../css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="../css/common_rtl.css">
<?php endif;?>
<title>Carpool CMS</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar(AuthHandler::isLoggedIn())?>
<?php echo View_Header::render(_('Carpool CMS'))?>
<div id="content">
<h2><?php echo _('Questions and Answers editor')?></h2>
<form id="qaDataForm" action="cms.php" method="post">
	<table style="width: 100%">
		<tr>
			<th>
    			<span><?php echo _('Language') ?></span>
    		</th>
			<th>
    			<span><?php echo _('Question') ?></span>
    		</th>
			<th>
    			<span><?php echo _('Answer') ?></span>
    		</th>		
		</tr>
<?php 

$locales = LocaleManager::getInstance()->getLocales();
$currentQuestions = DatabaseHelper::getInstance()->getQuestionsAnswers();

foreach($currentQuestions as $questionAnswerAllLangs) {
    $id = $questionAnswerAllLangs[LocaleManager::getDefaultLocale()]['Id'];
    foreach ($locales as $lang => $locale) {
        $questionAnswer = isset($questionAnswerAllLangs[$lang]) ? $questionAnswerAllLangs[$lang] : null;
?>
    	<tr>
    		<td>
    			<span><?php echo $locales[$lang]['Name'] ?></span>
    		</td>
        	<td>
        		<input type="text" id="question_<?php echo $id ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Question'] ?>" />
        	</td>
        	<td>
        		<input type="text" id="answer_<?php echo $id ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Answer'] ?>" />
        	</td>
    	</tr>
<?php 
    } 
}
?>
	</table>
	<h2><?php echo _('Add new')?></h2>
<?php 
foreach ($locales as $lang => $locale) {
?>	
	<table style="width: 100%">
		<tr>
			<th>
    			<span><?php echo _('Language') ?></span>
    		</th>
			<th>
    			<span><?php echo _('Question') ?></span>
    		</th>
			<th>
    			<span><?php echo _('Answer') ?></span>
    		</th>		
		</tr>	
	
    	<tr>
    		<td>
    			<span><?php echo $locales[$lang]['Name'] ?></span>
    		</td>
        	<td>
        		<input type="text" id="question_<?php echo $questionAnswer['Id'] ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Question'] ?>" />
        	</td>
        	<td>
        		<input type="text" id="answer_<?php echo $questionAnswer['Id'] ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Answer'] ?>" />
        	</td>
    	</tr>
	
<?php 
}
?>	
	
	</table>
	
	<dl class="noFloat">
    	<dd>
    		<input type="submit" value="<?php echo _('Submit')?>" />
    	</dd>
	</dl>
</form>
</div>
</div>
<script type="text/javascript" src="../lib/jquery-1.4.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="../js/utils.js"></script>
</body>
</html>
<?php } ?>