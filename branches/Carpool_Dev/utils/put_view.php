<?php 

include "../public/env.php";
include APP_PATH . "/Bootstrap.php";

// Simple class used for fast evaluation of view scripts

$validFileList = array();

$handle = opendir(APP_PATH . '/views');
    
if (!$handle) {
     die ('Could not open handle to views folder: ' . APP_PATH . '/views.');
}
while (($file = readdir($handle)) !== false) {
    if (substr($file, -4) === '.php') {
        $validFileList []= $file;
    }
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Carpool</title>
</head>
<body>
<div id="bd">
<div>
    <form method="post" action="<?php echo $_SERVER['PHP_SELF'] ?>">
        <p>
            <label for="fileName" style="width: 12em; float: left">File</label>
            <select id="fileName" name="fileName">
            <?php foreach($validFileList as $f): ?>
            	<option value="<?php echo $f ?>"><?php echo $f?></option>
            <?php endforeach;;?>
            </select>
        </p>
        <p>
            <label for="params" style="width: 12em; float: left">Parameters (key = value)</label>
            <textarea rows="5" cols="40" id="params" name="params"><?php if (isset($_POST['params'])) echo $_POST['params']?></textarea>
        </p>
        <p>
        	<input type="submit" value="Render" />
        </p>
    </form>
    <hr />
</div>
<?php 

if (isset($_POST['fileName'])) {
    $fileName = $_POST['fileName'];
    if (!in_array($fileName, $validFileList)) {
        die ('File was not found in the views folder');
    }
    $params = null;
    // TODO: Why JSON doesn't work?
    if (isset($_POST['params'])) {
        $paramStr = explode("\n", $_POST['params']);
        foreach ($paramStr as $pair) {
            $pairArr = explode('=', $pair);
            $pairArr[0] = trim($pairArr[0]);
            $pairArr[1] = trim($pairArr[1]);
            if (substr($pairArr[1], 0, 1) == '[' &&  substr($pairArr[1], -1) == ']') {
                $params[trim($pairArr[0])] = explode(',', $pairArr[1]);
            } else {
                $params[trim($pairArr[0])] = $pairArr[1];
            }
        }
    }
     
    ViewRenderer::render(APP_PATH . '/views/' . $fileName, $params);
}

?>
</div>
</body>
</html>