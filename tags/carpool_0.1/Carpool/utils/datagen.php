<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="lib/ac/jquery.autocomplete.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<title>Carpool</title>
</head>
<body>
<div id="bd">
<p>
	<label for="recordsCount">Records to generate</label>
	<input type="text" size=3 id="recordsCount" />
	<input type="button" id="doGenerateButton" value="Generate!" />
</p>
<p id="results"></p>
</div>
<script type="text/javascript" src="../public/lib/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="../public/js/utils.js"></script>
<script type="text/javascript">

var dataDesc = [
	{ name : 'name', type : 'str', minLength: 3, maxLength: 15 },
	{ name : 'email', type : 'str', minLength: 3, maxLength: 15 },
	{ name : 'phone', type : 'int', min: 1111, max: 9999999 },
	{ name : 'srcCityId', type : 'int', min: -2, max: 50 },
	{ name : 'destCityId', type : 'int', min: -2, max: 50 },
	{ name : 'srcLocation', type : 'str', minLength: 3, maxLength: 15, optional : true },
	{ name : 'destLocation', type : 'str', minLength: 3, maxLength: 15, optional : true },
	{ name : 'timeMorning', type : 'int', min: 5, max: 10, optional : true },
	{ name : 'timeEvening', type : 'int', min: 15, max: 22, optional : true },
	{ name : 'comment', type : 'str', minLength: 1, maxLength: 128, optional : true },
	{ name : 'wantTo', type: 'int', min: 1, max: 2 }	
];

function generateInt(min, max) {
	return min + Math.floor(Math.random()* Math.abs(max - min));
}

function generateString(minLength, maxLength) {
	var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');
	var str = '';
	var length = generateInt(minLength, maxLength);
    for (var i = 0; i < length; i++) {
        str += chars[Math.floor(Math.random() * chars.length)];
    }
    return str;
}

function putResult(str, stat) {
	if (typeof stat == 'undefined' || stat) {
		$('#results').append('<p style="color: green">' + str + '</p>');
	} else {
		$('#results').append('<p style="color: red; font-weight: bold">Error: ' + str + '</p>');
	}
}

function doGenerate() {
	var count = parseInt($('#recordsCount').val());
	if (!isNaN(count)) {
    	for (var i = 0; i < count; ++i) {
    		var params = {};
    		for (f in dataDesc) {
        		var res = '';
        		var field = dataDesc[f];
        		if (field.type === 'int') {
            		res = generateInt(field.min, field.max);
        		} else if (field.type === 'str') {
            		res = generateString(field.minLength, field.maxLength);
        		}
        		params[field.name] = res;
    		}
    		
    		$.post('http://localhost/pdt/Carpool/public/xhr/AddRideAll.php', params, function(xhr) {
    			status = xhr.status;
    			action = xhr.action;
    			if (status === 'ok') {
    				putResult('Ride added');
    			} else if (status === 'invalid') {
    				var str = '';
    				for (msg in xhr.messages) {
    					str += '' + xhr.messages[msg] + '; ';
    				}
    				if (xhr.messages.length > 0) {
    					str = str.substring(0, str.length - 2) + '.';
    				} 
    				putResult('Could not ' + action + ' ride: ' + str, false);
    			} else if (status === 'err') {
    				putResult('Could not ' + action + ' ride: Internal error. ' + (status.msg ? status.msg : ""), false);
    			} else {
    				putResult('Congrats! You broke everything!', false);
    			}		
    			$.get('http://localhost/pdt/Carpool/public/auth.php?action=logout', {} ,function(xhr) {
    			});		
    		}, 'json');

    		
    	}
	}
	
}

$(document).ready(function() {
	$.ajaxSetup({
		async : false
	});
	
	$('#doGenerateButton').click(doGenerate);
});

</script>
</body>
</html>