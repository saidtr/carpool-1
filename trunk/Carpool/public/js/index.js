
// Stores all search results
var searchResults = null;

// Helper function to create a table cell element with the given content
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

function statusCodeToText(/* Integer */status) {
	status = parseInt(status);
	switch (status) {
	case Constants.STATUS_LOOKING: return _('Looking');
	case Constants.STATUS_OFFERED: return _('Providing');
	default: 
		console.log('Illegal status code: ' + status);
		return '';
	}
}

function displayShowInterestDialog(/* Boolean */ show) {
	if (show) {
		positionShowInterest();
    	$('#loadingNotice').html('');
    	$('#showInterest').slideDown('fast', function() {
    		$('#email').focus();    		
    	});
	} else {
		$('#showInterest').slideUp('fast');
		updateShowInterestText();
	}
}

function updateShowInterestText() {
	var srcId = $('#srcCity').val();
	var destId = $('#destCity').val();
	
	//$('#loadingNotice').html('<a href="javascript:displayShowInterestDialog(true)" title="' +  _('Get mail notifications about new suitable rides.') + '">' + _('Notify me about new rides') + '</a>');
	
}

function buildSearchResults(/* JSON */ data) {
	// Clean all existing results
	$('#resultsTable tr:first').siblings().remove();
	$('#resultsMessage').text('');

	if (data.length === 0) {
		// Well, nothing
		$('#resultsTable').hide();
		$('#resultsMessage').text(_('Sorry, no results found.'));
	} else {
		$('#resultsTable').show();
		for (var res in data) {
			var ride = data[res];
			var elemStr = '<tr>';
			elemStr += cell(statusCodeToText(ride.Status));
			elemStr += cell(ride.SrcCity + (ride.SrcLocation && ride.SrcLocation !== '' ? ", " + ride.SrcLocation : ""));
			elemStr += cell(ride.DestCity + (ride.DestLocation && ride.DestLocation !== '' ? ", " + ride.DestLocation : ""));
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
	var wantTo = $('#wantTo').val();
	if (wantTo != Constants.STATUS_DONT_CARE)
		filter.addCriteria(new FilterCriteria('Status', wantTo, filterEquals));

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
	
	$("#searchForm").unbind('submit');

	$('#loadingNotice').text(_('Loading...'));
	$.get(Constants.xhr['SEARCH_RIDES'], {}, function(xhr) {
		$('#loadingNotice').text('');
		searchResults = xhr.results;
		doFilter();
		$("#wantTo").change(doFilter);
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
				showMessage(_('Thanks for showing interest! You will notified about new rides.'));
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
		showInterestFormOptions.data = { srcCityId : $('#srcCity').val(), destCityId : $('#destCity').val() };
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
