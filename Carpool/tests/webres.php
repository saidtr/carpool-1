<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>PHP Unit browser front-end</title>
</head>
<body>
<?php

require_once 'testenv.php';
require_once 'PHPUnit/Autoload.php';

//$unit = new PHPUnit();
$tests = array(
	'Test_Service_ShowInterest' => true, 
	'Test_AuthenticationHelperLdap' => true,
    'Test_AuthenticationHelperPassword' => true,
    'Test_SimpleAcl' => true,
	'Test_DatabaseHelper' => true
);

foreach ($tests as $test => $shouldRunTest) {
    if (!$shouldRunTest) continue;
    require "$test.php";
    
    $suite  = new PHPUnit_Framework_TestSuite($test);
    //$result = $unit->run($suite);
    
    echo "<h1>$test</h1>";
    echo "<pre>";
    PHPUnit_TextUI_TestRunner::run($suite);
    echo "</pre>";
    echo "<hr />";
}

?></body>
</html>