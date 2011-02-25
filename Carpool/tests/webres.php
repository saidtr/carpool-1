<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>PHP Unit browser front-end</title>
</head>
<body>
<?php

require_once 'testenv.php';
require_once 'PHPUnit.php';
require_once 'Test_Service_ShowInterest.php';

$suite  = new PHPUnit_TestSuite("Test_Service_ShowInterest");
$unit = new PHPUnit();
$result = $unit->run($suite);

echo $result->toHtml();
?></body>
</html>