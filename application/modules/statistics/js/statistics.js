function Statistics(optionArr){
	this.properties = {
		siteUrl: '/',
	};
	
	var _self = this;
	
	this.Init = function(options){
		_self.properties = $.extend(_self.properties, options);
		
        // TODO:
	};
	
	_self.Init(optionArr);
}
