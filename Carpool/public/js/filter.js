
function FilterCriteria(key, value, filterFunc) {
	this.key = key;
	this.value = value;
	this.filterFunc = filterFunc;
		
}

// TODO: Inner function? 
function filterEquals(a, b) { 
	return a == b; 
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
		var passedCriteria = true;
		var record = data[r];
		//document.write(record);
		for (k in this.criteria) {
			var key = this.criteria[k].key; 
			var val = this.criteria[k].value;
			//document.write(key + ' ' + val + '<br>');
			
			if (typeof record[key] != 'undefined') {
				if (!this.criteria[k].filterFunc(record[key], val)) {	
					passedCriteria = false;
					break;
				}
			}
		}
		if (passedCriteria)
			res.push(record);
	}
	return res;
};
