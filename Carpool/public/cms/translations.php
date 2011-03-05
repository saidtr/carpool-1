<?php

include "../env.php";
include APP_PATH . "/Bootstrap.php";

// This is a post - form submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = DatabaseHelper::getInstance();
    $locales = LocaleManager::getInstance()->getLocales();
    
    $toUpdate = array();
    $toInsert = array();
    foreach ($_POST as $key => $value) {
        // Valid fields are in the form of Type_Id_Language. We are only interested in 'question' or 'questionnew'
        if (!preg_match('/^(question|questionnew)_[0-9]+_' . LocaleManager::getDefaultLocale() . '/', $key)) {
            continue;
        }
        
        $keyParts = explode('_', $key);
        $func = $keyParts[0];
        $id = $keyParts[1];        // Id of the new/modified question
        $qId = $keyParts[1];       // Id of the input element
        
        // We only need to add if there is some data in the "New question" field
        $newQuestionAnswer = ($func === 'questionnew' && !Utils::isEmptyString($_POST['questionnew_0_' . LocaleManager::getDefaultLocale()]));
        if ($newQuestionAnswer) {
            $id = $db->getNextQuestionAnswerId();
            $qId = 0;
        } 
        foreach ($locales as $lang) {
            $answerFunc = str_replace('question', 'answer', $func);
            $question = isset($_POST[$func . '_' . $qId . '_' . $lang['Id']]) ? $_POST[$func . '_' . $qId . '_' . $lang['Id']] : null; 
            $answer = isset($_POST[$answerFunc . '_' . $qId . '_' . $lang['Id']]) ? $_POST[$answerFunc . '_' . $qId . '_' . $lang['Id']] : null;
            
            if ($newQuestionAnswer) {
                $db->insertQuestionAnswer($id, $lang['Id'], $question, $answer);
            } elseif ($func === 'question') {
                $db->updateQuestionAnswer($id, $lang['Id'], $question, $answer);
            }
            
        }       
    }   
    
    Utils::redirect('translations.php');
} else {

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
<form id="qaDataForm" action="translations.php" method="post">
	<table style="width: 100%" summary="<?php echo _('Edit existing translations')?>">
		<tr>
			<th>
    			<span><?php echo _('Language') ?></span>
    		</th>
			<th></th>		
    		<th></th>
		</tr>
<?php 

$locales = LocaleManager::getInstance()->getLocales();
$currentQuestions = DatabaseHelper::getInstance()->getQuestionsAnswers();

foreach($currentQuestions as $questionAnswerAllLangs) {
    $id = $questionAnswerAllLangs[LocaleManager::getDefaultLocale()]['Id'];
    $first = true;
    foreach ($locales as $lang => $locale) {
        $questionAnswer = isset($questionAnswerAllLangs[$lang]) ? $questionAnswerAllLangs[$lang] : null;
?>
    	<tr>
    		<td>
    			<span><?php echo $locales[$lang]['Name'] ?></span>
    		</td>
        	<td>
        		<input style="width: 100%;" type="text" id="question_<?php echo $id ?>_<?php echo $lang ?>" name="question_<?php echo $id ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Question'] ?>" />
        	</td>
        	<?php if ($first): ?>
        	<td rowspan="<?php echo count($locales)?>">
        		<input class="deleteButton" type="button" id="delete_<?php echo $id?>" value="Delete!" />
        	</td>
        	<?php $first = false; endif; ?>
    	</tr>
    	<tr>
    		<td></td>
        	<td>
        		<input style="width: 100%;" type="text" id="answer_<?php echo $id ?>_<?php echo $lang ?>" name="answer_<?php echo $id ?>_<?php echo $lang ?>" value="<?php echo $questionAnswer['Answer'] ?>" />
        	</td>

    	</tr>
<?php 
    } 
}
?>
	</table>
	<h2><?php echo _('Add new')?></h2>
	<table style="width: 100%" summary="<?php echo _('Add new translations')?>" >
		<tr>
			<th>
    			<span><?php echo _('Language') ?></span>
    		</th>
			<th></th>		
		</tr>	
<?php 
foreach ($locales as $lang => $locale) {
?>	
	
    	<tr>
    		<td>
    			<span><?php echo $locales[$lang]['Name'] ?></span>
    		</td>
        	<td>
        		<input type="text" style="width: 100%;" id="questionnew_0_<?php echo $lang ?>" name="questionnew_0_<?php echo $lang ?>" value="" />
        	</td>
        </tr>
        <tr>
        	<td>
        	</td>
        	<td>
        		<input type="text" style="width: 100%;" id="answernew_0_<?php echo $lang ?>" name="answernew_0_<?php echo $lang ?>"  value="" />
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