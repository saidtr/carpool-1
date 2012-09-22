
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
	case Constants.STATUS_LOOKING: return _('L');
	case Constants.STATUS_OFFERED: return _('P');
	case Constants.STATUS_SHARING: return _('S');
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
	//var srcId = $('#srcCity').val();
	//var destId = $('#destCity').val();
	
	//$('#loadingNotice').html('<a href="javascript:displayShowInterestDialog(true)" title="' +  _('Get mail notifications about new suitable rides.') + '">' + _('Notify me about new rides') + '</a>');
	
}

function buildComment(/* JSON */ ride) {
	var elemStr = '';
	
	var rideTimesStr = false;
	if (ride.TimeMorning && ride.TimeMorning != Constants.TIME_DIFFER) {
		rideTimesStr = true;
		if (ride.TimeMorning == Constants.TIME_IRRELAVANT) {
			elemStr += _('Arrival ride is not relevant');
		} else {
			elemStr += _('Usually leaves home at') + ' ' + formatTime(ride.TimeMorning);
		}
		elemStr += '. ';
	}
	if (ride.TimeEvening && ride.TimeEvening != Constants.TIME_DIFFER) {
		rideTimesStr = true;
		if (ride.TimeMorning == Constants.TIME_IRRELAVANT) {
			elemStr += _('Home ride is not relevant');
		} else {
			elemStr += _('Usually leaves work at') + ' ' + formatTime(ride.TimeMorning);
		}
		elemStr += '. ';
	}
	
	elemStr += '<p>' + (ride.Comment ? htmlEnc(ride.Comment) : '&nbsp;') + '</p>';
	
	if (!rideTimesStr) 
		elemStr += '<p>&nbsp;</p>';
	
	if (ride.TimeUpdated) {
		d = new Date(phpTimeToJsTime(ride.TimeUpdated));
		elemStr += '<p><b>' + _('Last Updated') + ':</b> ' + d.toLocaleDateString() + '</p>';
	}
	
	return elemStr;
}

function buildRideRow(/* JSON */ ride) {
	var elemStr = '';
	elemStr += cell(statusCodeToText(ride.Status));
	elemStr += '<td><p>' + htmlEnc(ride.Name) + '</p></td>';
	elemStr += cell(ride.SrcCity + (ride.SrcLocation && ride.SrcLocation !== '' ? ", " + ride.SrcLocation : ""));
	if (Constants.DISPLAY_DEST === '1') {
		elemStr += cell(ride.DestCity + (ride.DestLocation && ride.DestLocation !== '' ? ", " + ride.DestLocation : ""));
	}
	elemStr += '<td>';
	if (ride.Email) {
		elemStr += '<p>';
		elemStr += '<span class="contactDetailHeader">' + _('Mail') + ':</span><span class="contactDetail">' + mailTo(htmlEnc(ride.Email)) + '</span>';
		elemStr += '</p>';
	}
	if (ride.Phone) {
		elemStr += '<p>';
		elemStr += '<span class="contactDetailHeader">' + _('Phone') + ':</span><span class="contactDetail">' + htmlEnc(ride.Phone) + '</span>';
		elemStr += '</p>';
	} 
	elemStr += '</td>';
	elemStr += '<td>' + buildComment(ride) + '</td>';
	return elemStr;
	
}

function buildSearchResults(/* JSON */ data) {
	// Clean all existing results
	$('#resultsTable tr:first').siblings().remove();
	$('#resultsMessage').text('');
	if (!data || data.length === 0) {
		// Well, nothing
		$('#resultsTable').hide();
		$('#resultsMessage').text(_('Sorry, no results found.'));
	} else {
		$('#resultsTable').show();
		for (var res in data) {
			var ride = data[res];
			
			
			var elemStr = '<tr>';
			elemStr += buildRideRow(ride);
			/*
			elemStr += cell(statusCodeToText(ride.Status));
			elemStr += cell(ride.SrcCity + (ride.SrcLocation && ride.SrcLocation !== '' ? ", " + ride.SrcLocation : ""));
			if (Constants.DISPLAY_DEST === '1') {
				elemStr += cell(ride.DestCity + (ride.DestLocation && ride.DestLocation !== '' ? ", " + ride.DestLocation : ""));
			}
			elemStr += cell(formatTime(ride.TimeMorning));
			elemStr += cell(formatTime(ride.TimeEvening));
			elemStr += cell(ride.Name);
			elemStr += cell(mailTo(htmlEnc(ride.Email)), false);
			elemStr += cell(ride.Phone);
			elemStr += cell(ride.Comment);
			*/
			elemStr += '</tr>';
			
			$('#resultsTable').append(elemStr);
		}
		$('#resultsTable tr:not([th]):odd').css('background', '#E6E6FA');
	}
}

function doFilter() {
	var filter = new Filter();
	var srcId = $('#srcCity').val();
	if (srcId != Constants.LOCATION_DONT_CARE)
		filter.addCriteria(new FilterCriteria('SrcCityId', srcId, filterEquals));
	
	if (Constants.DISPLAY_DEST === '1') {
		var destId = $('#destCity').val();
		if (destId != Constants.LOCATION_DONT_CARE)            	
		    filter.addCriteria(new FilterCriteria('DestCityId', destId, filterEquals));
	}
	var wantTo = $('#wantTo').val();
	var statuses = [ wantTo ];
	if (wantTo != Constants.STATUS_SHARING) {
		statuses.push(Constants.STATUS_SHARING);
	}
	if (wantTo != Constants.STATUS_DONT_CARE)
		filter.addCriteria(new FilterCriteria('Status', statuses, filterInArray));

	buildSearchResults(filter.filter(searchResults, true));
	
	updateShowInterestText();
}

function doSearchAsYouType() {
	var filter = new Filter();
	var srcCity = $('#srcCityFilter').val();
	var destCity = $('#destCityFilter').val();
	
	if (srcCity !== '')
		filter.addCriteria(new FilterCriteria(['SrcCity', 'SrcLocation'], srcCity, filterStartsWith));

	if (destCity !== '')
		filter.addCriteria(new FilterCriteria(['DestCity', 'DestLocation'], destCity, filterStartsWith));

	buildSearchResults(filter.filter(searchResults));	
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
		if (xhr.status === 'ok') {
			searchResults = xhr.results;
			doFilter();
			$("#wantTo").change(doFilter);
			if (Constants.DISPLAY_DEST === '1') {
				$("#destCity").change(doFilter);
			}
			$("#srcCity").change(doFilter);
		} else {
			showError(_('Could not fetch rides') + ': ' + _('Internal Error') + '. ' + _('Please try again later.'));
		}
	}, 'json');
	
	//$("#srcCityFilter").keyup(doSearchAsYouType);
	//$("#destCityFilter").keyup(doSearchAsYouType);

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
				showError(_('Could not add ride') + ': ' + _('Internal error') + '. ' + (status.msg ? status.msg : ""));
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
	
	// Region selector
	$('#regionSelector').change(function() {
		$('#regionSelectorForm').submit();
	});

	// Register ajax error handler
	$(document).ajaxError(function(evt, xhr, settings, exception) {
		showError(_('Sorry, something went wrong. Request could not be completed.'));
	});
				
});
