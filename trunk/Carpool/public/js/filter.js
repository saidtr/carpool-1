
function FilterCriteria(key, value, filterFunc) {
	this.key = key;
	this.value = value;
	this.filterFunc = filterFunc;
		
}

function filterEquals(a, b) { 
	return a == b; 
}

function filterAnd(a, b) {
	return (a & b);
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

Filter.prototype.filter = function (/* Array */ data) {
	// Null criteria - simply return the whole data set
	if (this.criteria.length === 0)
		return data;
	
	var res = [];
	for (r in data) {		
		var passedCriteria = false;
		var record = data[r];
		
		for (k in this.criteria) {
			var keys;
			if (typeof this.criteria[k].key === 'string')
				keys = [ this.criteria[k].key ];
			else
				keys = this.criteria[k].key;
			var val = this.criteria[k].value;
			
			for (key in keys) {
				passedCriteria |= this.criteria[k].filterFunc(record[keys[key]], val);
			}
			
			// No need to keep looking if we already not there
			if (!passedCriteria)
				break;
		}
		if (passedCriteria)
			res.push(record);
	}
	return res;
};
