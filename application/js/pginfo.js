function pginfo(optionArr){
	this.properties = {
		messages : ''
	};

	var _self = this;

	this.Init = function(options){
		_self.properties = $.extend(_self.properties, options);
		_self.add_messages();
	};

	this.add_messages = function(){
		var elem;
		var msgElem;
		if (null === _self.properties.messages) {
			return false;
		}
		$.each(_self.properties.messages, function(type, messages) {
			if(typeof messages === 'object'){
				$.each(messages, function (key, message){
					elem = $('[name="' + message.name + '"]');
					if(0 === elem.length) {
						elem = $('#' + message.name);
					}
					if(0 !== elem.length) {
						elem.addClass('pginfo field ' + type);
					}
                    
                    if('success' == type) {
                        elem.parent().after('<i class="fa fa-check fa-2x field-success-icon" aria-hidden="true"></i>');
                    }
                    
                    if('error' == type) {
                        elem.parent().after('<i class="fa fa-close fa-2x field-error-icon" aria-hidden="true"></i>');
                    }

					msgElem = $('.pginfo.msg.' + message.name);
					if(0 === msgElem.length) {
						elem.after('<span class="pginfo msg ' + message.name + ' ' + type + '">' + message.text + '</span>');
					} else if (0 !== msgElem.length) {
						msgElem.addClass(type).html(message.text);
					}
				});
			}
		});
	};

	_self.Init(optionArr);
}