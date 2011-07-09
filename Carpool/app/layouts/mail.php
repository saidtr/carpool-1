<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<style type="text/css">
body {
	font:13px/1.231 arial, helvetica, clean, sans-serif;
}
div, p { 
	margin: 2px; 
	padding: 0; 
}
h1 { 
	font-size: 1.5em; 
	border-bottom: 1px solid #c0c0c0;
	margin: 2px 2px 5px 2px;
	padding: 0 0 2px 0;
} 
h2 { 
	font-size: 1.2em; 
}
</style>
</head>
<body>
<?php $this->body->doRender() ?>
<?php if (isset($this->optoutUrl)):?>
<div style="font-size: small; margin-top: 15px">
<?php printf (_('This is an automatic mail from %s. Click <a href="%s">here</a> if you do not want to get any more notifications from the site'), 'Carpool', $this->optoutUrl)?>
</div>
<?php endif; ?>
</body>
</html>
