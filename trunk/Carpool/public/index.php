<?php 

include "env.php";
include APP_PATH . "/Bootstrap.php";

$db = DatabaseHelper::getInstance();

AuthHandler::putUserToken();

//$availableDestCities = $db->getAvailableCities('Dest');
//$availableSrcCities = $db->getAvailableCities('Src');
$availableDestCities = $db->getCities();
$availableSrcCities = $db->getCities();

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
<?php echo View_Navbar::buildNavbar(AuthHandler::isLoggedIn())?>
<?php echo View_Header::render(_('Lookin\' for a ride?'))?>
<div id="content">
	<div id="searchFormHolder">
		<form id="searchForm" action="xhr/SearchRides.php">
			<fieldset>
			<legend></legend>
			<dl>
				<!-- 
				<dd>
					<label for="wantTo">Show me&nbsp;</label>
					<select id="wantTo" name="wantTo">
						<option value="<?php echo STATUS_DONT_CARE?>">Everyone</option>
						<option value="<?php echo STATUS_OFFERED ?>">Available rides</option>
						<option value="<?php echo STATUS_LOOKING  ?>">People looking for a ride</option>
					</select>
				</dd>
				 -->
				<dd>
					<label for="srcCity"><?php echo _('Show me rides from')?>&nbsp;</label>
					<select id="srcCity" name="srcCity">
						<option value="<?php echo LOCATION_DONT_CARE ?>"><?php echo _('Everywhere')?></option> 
						<?php foreach($availableSrcCities as $city):?>
						<option value="<?php echo $city['Id']?>"><?php echo htmlspecialchars($city['Name'])?></option>
						<?php endforeach; ?>
					</select>
				
				</dd>
				<dd>
					<label for="destCity"><?php echo _('To')?>&nbsp;</label>
					<select id="destCity" name="destCity">
						<option value="<?php echo LOCATION_DONT_CARE ?>"><?php echo _('Everywhere')?></option>
						<?php foreach($availableDestCities as $city):?>
						<option value="<?php echo $city['Id']?>"><?php echo htmlspecialchars($city['Name'])?></option>
						<?php endforeach; ?>
					</select>
				</dd>	
				<dd class="hidden">
					<input type="submit"/>
				</dd>	
				<dd>
					<p id="loadingNotice"></p>
				</dd>		
			</dl>
			</fieldset>
		</form>
    	<div id="showInterest">
    		<span><a href="javascript:displayShowInterestDialog(false)"><?php echo _('Show interest')?></a></span>
    		<!-- <span id="showInterestClose">[X]</span> -->
    		<form id="showInterestForm" method="post" action="xhr/ShowInterest.php">
    			<dl class="noFloat">
        			<dd class="mandatory">
            			<label><?php echo _('Name')?></label>
            			<input type="text" name="name" id="name" />
        			</dd>
        			<dd class="mandatory">
            			<label><?php echo _('Email')?></label>
            			<input type="text" name="email" id="email" />
            		</dd>
        			<dd>
        				<input type="hidden" name="wantTo" value="<?php echo STATUS_LOOKING ?>"/>
        				<input type="submit" value="<?php echo _('Go!')?>" />
        			</dd>
    			</dl>
    		</form>
    	</div>					
	</div>
	<div id="results">
		<table id="resultsTable">
			<tr>
				<th id="resultsFrom"><?php echo _('From')?></th>
				<th id="resultsTo"><?php echo _('To')?></th>
				<th id="resultsIn"><?php echo _('In')?></th>
				<th id="resultsOut"><?php echo _('Out')?></th>
				<th id="resultsContact"><?php echo _('Name')?></th>
				<th id="resultsEmail"><?php echo _('Email')?></th>
				<th id="resultsPhone"><?php echo _('Phone')?></th>
				<th id="resultsComment"><?php echo _('Comment')?></th>
			</tr>
		</table>
		<p id="resultsMessage"></p>
	</div>
</div>
</div>
<?php 
View_Php_To_Js::putTranslations(array(
	'Sorry, something went wrong. Request could not be completed.',
	'Loading...'
));
View_Php_To_Js::putVariable('cities', $db->getCities());
View_Php_To_Js::putConstant('DEFAULT_DOMAIN', getConfiguration('default.domain'));
View_Php_To_Js::putConstant('APP_NAME', _(getConfiguration('app.name')));
View_Php_To_Js::putTranslations(
    array(
    	'Sorry, no results found.', 
    	'Show interest in this ride',
    	'Sorry, something went wrong. Request could not be completed.',
        'N/A',
        'Differs'
    )
);
echo View_Php_To_Js::render();
?>
<script type="text/javascript" src="lib/jquery-1.4.2.min.js"></script>
<script type="text/javascript" src="lib/form/jquery.form.min.js"></script>
<script type="text/javascript" src="js/utils.js"></script>
<script type="text/javascript" src="js/filter.js"></script>
<script type="text/javascript">

var citiesMapper = null;
var searchResults = null;

function cell(str, escape) {
	if ((typeof escape == 'undefined') || escape == true) {
		return '<td>' + htmlEnc(str) + '</td>';
	} else {
		return '<td>' + str + '</td>';
	}
}

/**
 * Build an HTML mailto element
 *
 * @param str Username or mail address of the recepient 
 */
function mailTo(str) {
	var addr = str;

	// If there's no domain in the mail, use the default one (john -> john@mycompany.com) 
	if (addr.indexOf('@') === -1) {
		addr += '@' + Constants.DEFAULT_DOMAIN;
	}
	return '<a href="mailto:' + addr + '?subject=' + Constants.APP_NAME + '">' + str + '</a>';
}

/**
 * Translates an hour given as integer to two-digit string representation or
 * description string
 */
function formatTime(/* String */ hour) {
	if (hour === null || hour == Constants.TIME_IRRELAVANT) {
		return _('N/A');
	} else if (hour == Constants.TIME_DIFFER) {
		return _('Differs');
	} else {
		return (hour.length == 1 ? '0' : '') + hour + ':00';
	} 
}

function displayShowInterestDialog(/* Boolean */ show) {
	if (show) {
		positionShowInterest();
    	$('#loadingNotice').html('');
    	$('#showInterest').slideDown('fast');
	} else {
		$('#showInterest').slideUp('fast');
		updateShowInterestText();
	}
}

function updateShowInterestText() {
	var srcId = $('#srcCity').val();
	var destId = $('#destCity').val();
	
	if (srcId != Constants.LOCATION_DONT_CARE || destId != Constants.LOCATION_DONT_CARE)
		$('#loadingNotice').html('<a href="javascript:displayShowInterestDialog(true)" title="' +  _('Get mail notifications about new suitable rides.') + '">' + _('Show interest') + '</a>');
	else
		$('#loadingNotice').html('');	
	
}

function buildSearchResults(/* JSON */ data) {
	// Clean everything
	$('#resultsTable tr:first').siblings().remove();
	$('#resultsMessage').text('');

	if (data.length === 0) {
		$('#resultsTable').hide();
		$('#resultsMessage').text(_('Sorry, no results found.'));
	} else {
		$('#resultsTable').show();
		for (var res in data) {
			var ride = data[res];
			var elemStr = '<tr>';
			elemStr += cell(citiesMapper[ride.SrcCityId] + (ride.SrcLocation !== '' ? ", " + ride.SrcLocation : ""));
			elemStr += cell(citiesMapper[ride.DestCityId] + (ride.DestLocation !== '' ? ", " + ride.DestLocation : ""));
			elemStr += cell(formatTime(ride.TimeMorning));
			elemStr += cell(formatTime(ride.TimeEvening));
			elemStr += cell(ride.Name);
			elemStr += cell(mailTo(htmlEnc(ride.Email)), false);
			elemStr += cell(ride.Phone);
			elemStr += cell(ride.Comment);
			elemStr += '</tr>';
			$('#resultsTable').append(elemStr);
		}
		$('#resultsTable tr:not([th]):odd').css('background', '#E6E6FA');
	}
}

function doFilter() {
	if (!searchResults) 
		return;

	var filter = new Filter();
	var srcId = $('#srcCity').val();
	if (srcId != Constants.LOCATION_DONT_CARE)
		filter.addCriteria(new FilterCriteria('SrcCityId', srcId, filterEquals));
	var destId = $('#destCity').val();
	if (destId != Constants.LOCATION_DONT_CARE)            	
	    filter.addCriteria(new FilterCriteria('DestCityId', destId, filterEquals));

	buildSearchResults(filter.filter(searchResults));
	
	updateShowInterestText();
}

function positionShowInterest() {
	var showInterestHolderOffset = $('#loadingNotice').offset();
	if (isRtl()) {
		showInterestHolderOffset.left -= ($('#showInterest').width() - $('#loadingNotice').width());
	}

	// Using $.offset cause problems with chrome
	$('#showInterest').css({top: showInterestHolderOffset.top + 'px', left: showInterestHolderOffset.left + 'px'});		
}

$(document).ready(function() {

	// Populate city list
	citiesMapper = [];
	for (city in cities) {
		citiesMapper[cities[city].Id] = cities[city].Name;
	}
	
	$("#searchForm").unbind('submit');

	$('#loadingNotice').text(_('Loading...'));
	$.get(Constants.xhr['SEARCH_RIDES'], {}, function(xhr) {
		$('#loadingNotice').text('');
		searchResults = xhr.results;
		doFilter();
		$("#destCity").change(doFilter);
		$("#srcCity").change(doFilter);
	}, 'json');

	// Ajax form
	var showInterestFormOptions = { 
		type         : 'POST', 
		dataType     : 'json',
		beforeSubmit : function(formData, jqForm, options) {
			return true;
		},
		success      : function(xhr) {
			status = xhr.status;
			action = xhr.action;
			
			if (status === 'ok') {
				showMessage(_('Thanks for your interest! You will notified about new rides.'));
				displayShowInterestDialog(false);
			} else if (status === 'invalid') {
				var str = '';
				for (msg in xhr.messages) {
					str += '' + xhr.messages[msg] + '; ';
				}
				if (xhr.messages.length > 0) {
					str = str.substring(0, str.length - 2) + '.';
				} 
				showError(_('Could not add ride') + ': ' + str);	
			} else if (status === 'err') {
				showError(_('Could not add ride: Internal error. ' + (status.msg ? status.msg : "")));
			} else {
				showError(_('Congrats! You broke everything!'));
			}
			positionShowInterest();
		}
	}; 

	$('#showInterestForm').submit(function() {
		showInterestFormOptions.data = { srcCityId : 1, destCityId : 1 };
		$('#showInterestForm').ajaxSubmit(showInterestFormOptions);
		return false;
	});

	positionShowInterest();
	
	$('#showInterestClose').click(function() {
		displayShowInterestDialog(false);
	});

	// Register ajax error handler
	$(document).ajaxError(function(evt, xhr, settings, exception) {
		showError(_('Sorry, something went wrong. Request could not be completed.'));
	});
				
});

</script>
</body>
</html>