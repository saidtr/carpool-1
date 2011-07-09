
function FilterCriteria(key, value, filterFunc) {
	this.key = key;
	this.value = value;
	this.filterFunc = filterFunc;	
}

function filterEquals(a, b) { 
	return a == b; 
}

function filterAnd(a, b) {
	console.log('FilterAnd: ' + a + '&' + b + ': ' + (a&b));
	return (a & b) != 0;
}

function filterStartsWith(a, b) {
	if ((typeof a !== 'string') || (typeof b !== 'string')) 
		return false;
	return a.toLowerCase().indexOf(b.toLowerCase()) === 0;
}

function Filter(params) {

	this.criteria = [];
	for (cr in params) {
		this.criteria.push(params[cr]);
	}
	
};

Filter.prototype.addCriteria = function(/* FilterCriteria */ cr) {
	this.criteria.push(cr);
};

Filter.prototype.filter = function (/* Array */ data, /* Boolean */ mustMatchAll) {
	// Null criteria - simply return the whole data set
	if (this.criteria.length === 0)
		return data;
	
	var res = [];
	for (r in data) {		
		// Is this record passing?
		// In "must match all" mode, we start with "yes" and fail on mismatch.
		// Otherwise, we'll start with "no" and look for match
		var passedCriteria = mustMatchAll;
		var record = data[r];
		
		for (k in this.criteria) {
			var keys;
			if (typeof this.criteria[k].key === 'string')
				keys = [ this.criteria[k].key ];
			else
				keys = this.criteria[k].key;
			var val = this.criteria[k].value;
			
			for (key in keys) {
				if (mustMatchAll)
					passedCriteria &= this.criteria[k].filterFunc(record[keys[key]], val);
				else
					passedCriteria |= this.criteria[k].filterFunc(record[keys[key]], val);
			}
			
			// No need to keep looking if we already not there
			if (passedCriteria === !mustMatchAll)
				break;
		}
		if (passedCriteria)
			res.push(record);
	}
	return res;
};
