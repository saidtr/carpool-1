<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<style type="text/css">
h1 { font-size: xx-large; } 
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
