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
require_once 'Test_AuthenticationHelperLdap.php';
require_once 'Test_AuthenticationHelperPassword.php';
require_once 'Test_SimpleAcl.php';
require_once 'Test_QueryBuilder.php';

$unit = new PHPUnit();
/*
$tests = array(
	'Test_Service_ShowInterest', 
	'Test_AuthenticationHelperLdap',
    'Test_AuthenticationHelperPassword',
    'Test_SimpleAcl'
);
*/
$tests = array(
    'Test_QueryBuilder'
);

foreach ($tests as $test) {
    $suite  = new PHPUnit_TestSuite($test);
    $result = $unit->run($suite);
    echo "<h1>$test</h1>";
    echo $result->toHtml();
    echo "<hr />";
}

?></body>
</html>