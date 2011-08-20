<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Carpool Error</title>
</head>
<body>
<div id="bd">
<h1>Internal Application Error</h1>
<h3>
The application cannot work due to an internal error.<br />
Details of this error have been logged. If the problem persists, please contact the site administrators.
</h3>
<?php if (ENV === ENV_DEVELOPMENT): ?>
    <?php if ($lastError = error_get_last()): ?>
    <p>Error <?php echo $lastError['type']?> in file <?php echo $lastError['file']?>, line <?php echo $lastError['line']?></p>
    <p><b>Details:</b>&nbsp;<?php echo $lastError['message']?></p>    
    <?php endif; ?>
    <?php if (isset($this->exception)): ?>
    <p><b>Message:</b>&nbsp;<?php echo $this->exception->getMessage() ?></p>
    <pre><?php echo $this->exception->getTraceAsString() ?></pre>
    <?php endif;?>    
<?php endif; ?>
</div>
</body>
</html>