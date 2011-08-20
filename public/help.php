<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

AuthHandler::putUserToken();

$content = DatabaseHelper::getInstance()->getQuestionsAnswersByLang(LocaleManager::getInstance()->getSelectedLanaguageId());

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
<?php echo View_Header::render(null)?>
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
<script type="text/javascript" src="lib/jquery-1.6.2.min.js"></script>
<?php echo View_Php_To_Js::render();?>
<script type="text/javascript" src="js/utils.js"></script>
</body>
</html>