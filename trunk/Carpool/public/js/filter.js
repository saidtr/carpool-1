
function FilterCriteriaByKey(key, value, filterFunc) {
	this.key = key;
	this.value = value;
	this.filterFunc = filterFunc;	
	this.type = 'FilterCriteriaByKey';
}

function FilterCriteriaByRecord(filterFunc) {
	this.filterFunc = filterFunc;
	this.type = 'FilterCriteriaByRecord';
}

function filterEquals(a, b) { 
	return a == b; 
}

function filterInArray(a, b) {
	var len = b.length;
	for(var i = 0; i < len; i++) {
		if(b[i] == a) return true;
	}
	return false;
}

function filterAnd(a, b) {
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
		
		// Run all filters on the current record.
		// There are two supported types of filters:
		// - By record - this is a flexible filter, allowing the caller to provide any
		//   callback that gets the whole record as argument
		// - By key - filter by a specific key
		for (k in this.criteria) {
			
			if (this.criteria[k].type === 'FilterCriteriaByRecord') {
				
				if (mustMatchAll)
					passedCriteria &= this.criteria[k].filterFunc(record);
				else
					passedCriteria |= this.criteria[k].filterFunc(record);
				
			} else if (this.criteria[k].type === 'FilterCriteriaByKey') {

				var key = this.criteria[k].key;
				var val = this.criteria[k].value;
				
				if (mustMatchAll)
					passedCriteria &= this.criteria[k].filterFunc(record[key], val);
				else
					passedCriteria |= this.criteria[k].filterFunc(record[key], val);
				
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
