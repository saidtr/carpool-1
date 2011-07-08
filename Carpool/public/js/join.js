
var citiesMapper = null;
var isWorking = false;

function disableButtons(disable) {
	var action = (disable) ? 'disabled' : '';
	$('input[type="button"],input[type="submit"]').attr('disabled', action);
	isWorking = disable;
	if (disable)
		$('#submitStatus').text(_('Working...'));
	else
		$('#submitStatus').text('');
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

function initAutocomplete() {
	$("#srcCity, #destCity").autocomplete(cities, {
		formatItem: function(item) {
			return htmlEnc(item.Name);
		}
	});
	$("#srcCity").result(function(event, item) {
		if (item) {
			$("#srcCity").val(htmlUnescape(item.Name));
			$("#srcCityId").val(item.Id);
		} else {
			$("#srcCityId").val(Constants.LOCATION_NOT_FOUND);
		}
	});
	$("#destCity").result(function(event, item) {
		if (item) {
			$("#destCity").val(htmlUnescape(item.Name));
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
			var status = xhr.status;
			var action = xhr.action;

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
		disableButtons(false);
	});

	disableButtons(false);
		
});
