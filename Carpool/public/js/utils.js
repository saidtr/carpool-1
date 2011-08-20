
// Define a default console logger to avoid errors
if (typeof console === 'undefined' || !console.log) {
	var console = {};
	console.log = function(/* String */ str) {};
}

function htmlEnc(str) {
	if (!str) return '';
	str = str.replace(/&/g, "&amp;");
	str = str.replace(/</g, "&lt;");
	str = str.replace(/>/g, "&gt;");
	str = str.replace(/\"/g, "&quot;");
	str = str.replace(/\'/g, "&#x27;");
	str = str.replace(/\//g, "&#x2F;");
	return str;
}

// Translate HTML entities to the corresponding characters. Use with caution! 
function htmlUnescape(str) {
	// Use the browser DOM capabilities for a simple translation
	return $('<div/>').html(str).text(); 
}

function refresh(timeout) {
	if (typeof (timeout) !== 'undefined') {
		setTimeout("refresh()", timeout);
	} else {
		location.reload(true);
	}
}

function redirect(page) {
	window.location = page;
}

function populateSelectBox(/* String */ selectBoxId, /* Array */ elems) {
	var sel = document.getElementById(selectBoxId);
	sel.options.length = 0;
    $.each(elems, function () {
    	sel.options[sel.options.length] = new Option(this.name, this.id);
    });
}

function isRtl() {
	return $('body').css('direction') === 'rtl';
}

/** Global message */

function showMessage(msg) {
	$('#globalMessage').removeClass('error');
	$('#messageBody').text(msg);
	$('#globalMessage').show();
}

function showError(msg) {
	$('#globalMessage').addClass('error');
	$('#messageBody').text(msg);
	$('#globalMessage').show();
}

function hideMessage() {
	$('#globalMessage').slideUp('fast');
}

/** Translations */

function _(/* String */ str) {
	if (typeof (Translations) !== 'undefined' && Translations[str]) {
		return Translations[str];
	} else {
		return str;
	}
}
	
$(document).ready(function() {
	// 'Close' button of the global message
	$('#closeMessage').click(function() {
		hideMessage();
	});
	
	// Mark mandatory form fields 
	$('dd.mandatory > label').append('<span class="mandatoryMark">*</span>');
	
	// Language selector
	$('#lang').change(function() {
		$('#langSelectorForm').submit();
	});
	
	/*
	// Region selector
	$('#region').change(function() {
		$('#regionSelectorForm').submit();
	});
	*/
});




