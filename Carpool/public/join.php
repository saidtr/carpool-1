<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

AuthHandler::putUserToken();

$db = DatabaseHelper::getInstance();

$contact = AuthHandler::getLoggedInUser();

$isLogged = false;
$isActive = true;

if ($contact) {
	extract($contact, EXTR_PREFIX_ALL, 'contact');
	$rideData = $db->getRideByContactId($contact_Id);
	extract($rideData, EXTR_PREFIX_ALL, 'ride');
	$isLogged = true;
	$isActive = ($ride_Status == STATUS_OFFERED);
}

?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="css/reset-fonts.css">
<link rel="stylesheet" type="text/css" href="lib/ac/jquery.autocomplete.css">
<link rel="stylesheet" type="text/css" href="css/common.css">
<?php if (LocaleManager::getInstance()->isRtl()):?>
<link rel="stylesheet" type="text/css" href="css/common_rtl.css">
<?php endif;?>
<title>Carpool</title>
</head>
<body>
<div id="bd">
<?php echo View_Navbar::buildNavbar($isLogged)?>
<?php
$header = ($isLogged) ? _("Hello again, ") . htmlspecialchars($contact_Name) : _("Provide a new ride.");
echo View_Header::render($header); 
?>
<div id="content">
	<div>
	<form id="addRideForm" action="#" method="post" onsubmit="return false;">
		<!--  
		<fieldset>
			<legend>I want to...</legend>
			<input type="radio" name="wantTo" checked="checked" value="<?php echo STATUS_LOOKING ?>"/>Join a ride
			<input type="radio" name="wantTo" value="<?php echo STATUS_OFFERED ?>"/>Provide a ride
		</fieldset>
		-->
		<p id="formMessage"></p>
		<fieldset>
			<legend>Ride details</legend>
			<dl>
				<dd class="mandatory">
					<label for="srcCity"><?php echo _('Coming from city')?></label>
					<input class="textInput" id="srcCity" name="srcCity" type="text" size=16 value="" />
				</dd>
				<dd class="optional">
					<label for="srcLocation"><?php echo _('Location')?></label>
					<input id="srcLocation" name="srcLocation" type="text" size=30 value="<?php echo (isset($ride_SrcLocation) ? $ride_SrcLocation : '') ?>" />
				</dd>
			</dl>
			<div class="clearFloat"></div>
			<input id="srcCityId" name="srcCityId" type="hidden" value="<?php echo (isset($ride_SrcCityId) ? $ride_SrcCityId : LOCATION_NOT_FOUND) ?>"/>
			<dl>
				<dd class="mandatory">
					<label for="destCity"><?php echo _('To')?></label>
					<input class="textInput" id="destCity" name="destCity" type="text" size=16 value=""/>
				</dd>
				<dd class="optional">
					<label for="destLocation"><?php echo _('Location')?></label>
					<input id="destLocation" name="destLocation" type="text" size=30 value="<?php echo (isset($ride_destLocation) ? $ride_destLocation : 'Checkpoint NBX') ?>"/>
				</dd>
			</dl>
			<input id="destCityId" name="destCityId" type="hidden" value="<?php echo (isset($ride_DestCityId) ? $ride_DestCityId : 57) ?>"/>
			<div class="clearFloat"></div>
			<dl>
				<dd class="optional">
					<label for="timeMorning"><?php echo _('Coming')?></label>
					<select id="timeMorning" name="timeMorning">
						<option selected="selected" value="<?php echo TIME_DIFFERS ?>"><?php echo _('It differs')?></option>
						<?php for($i = 5; $i <= 10; ++$i): ?>
						<option value="<?php echo $i?>" <?php if (isset($ride_TimeMorning) && $ride_TimeMorning == $i) echo 'selected="selected"'?>><?php echo Utils::FormatTime($i) ?></option>
						<?php endfor; ?>
						<option value="<?php echo TIME_IRRELEVANT ?>" <?php if (isset($ride_TimeMorning) && $ride_TimeMorning == TIME_IRRELEVANT) echo 'selected="selected"'?>><?php echo _('Irrelevant')?></option>
					</select>
				</dd>			
				<dd class="optional">
					<label for="timeEvening"><?php echo _('Leaving')?></label>
					<select id="timeEvening" name="timeEvening">
						<option value="<?php echo TIME_DIFFERS ?>"><?php echo _('It differs')?></option>
					    <?php for($i = 15; $i <= 22; ++$i): ?>
						<option value="<?php echo $i?>" <?php if (isset($ride_TimeEvening) && $ride_TimeEvening == $i) echo 'selected="selected"'?>><?php echo Utils::FormatTime($i) ?></option>
						<?php endfor; ?>
						<option value="<?php echo TIME_IRRELEVANT ?>" <?php if (isset($ride_TimeEvening) && $ride_TimeEvening == TIME_IRRELEVANT) echo 'selected="selected"'?>><?php echo _('Irrelevant')?></option>
					</select>
				</dd>			
			</dl>
			<div class="clearFloat"></div>			
		</fieldset>
		<fieldset>
			<legend>Contact details</legend>
			<dl class="noFloat">
				<dd class="mandatory">
					<label for="name"><?php echo _('Name')?></label>
					<input class="textInput" id="name" name="name" type="text" size=30 value="<?php echo (isset($contact_Name) ? $contact_Name : '')?>" />
				</dd>
				<dd class="mandatory">
					<label for="email"><?php echo _('Email')?></label>
					<input class="textInput" id="email" name="email" type="text" size=20 value="<?php echo (isset($contact_Email) ? $contact_Email : '')?>" />
				</dd>
				<dd>
					<label for="phone"><?php echo _('Phone')?></label>
					<input class="textInput" id="phone" name="phone" type="text" size=10 value="<?php echo (isset($contact_Phone) ? $contact_Phone : '')?>" />
				</dd>				
				<dd>
					<label for="comment"><?php echo _('Comments')?></label>
					<textarea id="comment" name="comment" rows=2 cols=40><?php echo (isset($Comment) ? $Comment : '')?></textarea>
				</dd>
			</dl>
		</fieldset>	
		<fieldset>
			<legend><?php echo _('Submit')?></legend>
			<input type="submit" value="<?php echo ($isLogged) ? _('Update') : _('Go!') ?>" />
			<?php if ($isLogged): ?>
			<input type="button" id="deleteButton" value="<?php echo _('Delete')?>!" />
			<input type="button" id="activateToggleButton" value="<?php echo ($isActive ? _("Deactivate") : _("Activate"))?>" />
			<?php endif; ?>
		</fieldset>
	</form>
	</div>
</div>
</div>
<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="lib/packed/join.pack.js"></script>
<?php
View_Php_To_Js::putTranslations(array(
	'Are you sure you want to completely, fully, eternally delete your ride?',
	'An error occured.',
	'Could not add ride',
	'Could not update ride',
	'Sorry, something went wrong.',
	'Congrats! You broke everything!'
)); 
echo View_Php_To_Js::render();

?>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript">

var cities = <?php echo json_encode($db->getCities())?>;
var citiesMapper = null;
var isWorking = false;

function disableButtons(disable) {
	var action = (disable) ? 'disabled' : '';
	$('input[type="button"],input[type="submit"]').attr('disabled', action);
	isWorking = disable;
}

function deleteRide() {
	var delConfirm = confirm(_("Are you sure you want to completely, fully, eternally delete your ride?"));
	if (delConfirm) {
		disableButtons(true);
    	$.post(Constants.xhr['DEL_RIDE'], null, function(data) {
    		refresh();
    	});
	} 
}

function initAutocomplete(/* JSON cities */) {
	$("#srcCity, #destCity").autocomplete(cities, {
		formatItem: function(item) {
			return htmlEnc(item.Name);
		}
	});
	$("#srcCity").result(function(event, item) {
		if (item) {
			$("#srcCityId").val(item.Id);
		} else {
			$("#srcCityId").val(Constants.LOCATION_NOT_FOUND);
		}
	});
	$("#destCity").result(function(event, item) {
		if (item) {
			$("#destCityId").val(item.Id);
		} else {
			$("#destCityId").val(Constants.LOCATION_NOT_FOUND);
		}
	});

	$("#srcCity, #destCity").change(function() {
		$(this).search();
	});	
}

function doActivateToggle() {
	disableButtons(true);
	$.get(Constants.xhr['TOGGLE_ACTIVATE'], {}, function(xhr) {
		if (xhr.status == 'ok') {
			refresh();
		} else {
			disableButtons(false);
			showError(_('An error occured.'));
		}
	}, 'json');
}

$(document).ready(function() {

	initAutocomplete();

	citiesMapper = [];
	for (city in cities) {
		citiesMapper[cities[city].Id] = cities[city].Name;
	}

	// Set the cities names according to the city id
	
	if ($("#srcCityId").val() !== Constants.LOCATION_NOT_FOUND) {
		$("#srcCity").val(citiesMapper[$("#srcCityId").val()]);
	}

	if ($("#destCityId").val() !== Constants.LOCATION_NOT_FOUND) {
		$("#destCity").val(citiesMapper[$("#destCityId").val()]);
	}	
	
	$("#addRideForm").unbind('submit');
	$("#addRideForm").attr('action', Constants.xhr['ADD_RIDE']);

	var del = $("#deleteButton");
	if (del) {
		del.click(deleteRide);
	}

	// Ajax form
	var addRideFormOptions = { 
		type         : 'POST', 
		dataType     : 'json',
		beforeSubmit : function() {
			if (isWorking)
				return false;

			valid = true;

			disableButtons(true);
			
			return valid;
		},
		success      : function(xhr) {
			status = xhr.status;
			action = xhr.action;
			if (status === 'ok') {
				if (action === 'add') {
					redirect('thanks.php');
				} else {
					refresh();
				}
			} else if (status === 'invalid') {
				var str = '';
				for (msg in xhr.messages) {
					str += '' + xhr.messages[msg] + '; ';
				}
				if (xhr.messages.length > 0) {
					str = str.substring(0, str.length - 2) + '.';
				} 
				if (action === 'add')
					showError(_('Could not add ride') + ': ' + str);
				else if (action === 'update')
					showError(_('Could not update ride') + ': ' + str);

				disableButtons(false);
			} else if (status === 'err') {
				showError('Could not ' + action + ' ride: Internal error. ' + (status.msg ? status.msg : ""));
				disableButtons(false);
			} else {
				showError(_('Congrats! You broke everything!'));
				disableButtons(false);
			}
		}
	}; 
	
	$("#addRideForm").ajaxForm(addRideFormOptions);

	$("#activateToggleButton").click(doActivateToggle);

	// Register ajax error handler
	$(document).ajaxError(function(evt, xhr, settings, exception) {
		showError(_('Sorry, something went wrong.'));
	});

	disableButtons(false);
		
});

</script>
</body>
</html>