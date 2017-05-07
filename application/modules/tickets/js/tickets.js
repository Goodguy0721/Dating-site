function tickets(optionArr){
	this.properties = {
		siteUrl: '/',
		idUser: 0,
		messagesContentDiv: '#tickets_content',
		messagesInput: '#messages_admin',
		messagesContentUrl: 'tickets/ajax_get_messages',
		messagesSendUrl: 'tickets/ajax_send_messages',
		messagesLoadLimit: 10,
		messagesCountPage: 0,
		messagesButtonMore: '',
		messagesButtonSend: '#send_message',
		messagesTruncate: 30,
	};
	
	var _self = this;
	
	this.Init = function(options){
		_self.properties = $.extend(_self.properties, options);
		_self.init_objects();
		_self.init_controls();		
		_self.load_message();	
	};
	
	this.uninit = function(){
		_self.send_message_btn_obj.off('click');
		_self.messages_content_obj.off('click', '#next-page');
	}
	
	this.init_objects = function(){
		_self.messages_content_obj = $(_self.properties.messagesContentDiv);
		_self.send_message_btn_obj = $(_self.properties.messagesButtonSend);
		_self.send_message_input_obj = $(_self.properties.messagesInput);
	}
	
	this.init_controls = function(){
		_self.send_message_btn_obj.off('click').on('click', function(){
			_self.send_message();
		})
		_self.messages_content_obj.off('click', '#next-page').on('click', '#next-page', function(){
			$(this).remove();
			_self.list_message();
		})
	}
	
	this.load_message = function(){
			_self.list_message();
    }
	
	this.list_message = function(){
		$.ajax({
			url: _self.properties.siteUrl + _self.properties.messagesContentUrl,
			type: 'POST',
			dataType : 'json',
			data: {count_messages:_self.properties.messagesCountPage},
			cache: false,
			success: function(resp){
				if(resp.content){
					_self.messages_content_obj.html(resp.content).fadeIn();
					_self.properties.messagesCountPage = $('.addressbar:visible').length;	
					if(_self.properties.messagesCountPage >= _self.properties.messagesLoadLimit){
						_self.messages_content_obj.append('<input class="btn btn-primary-inverted" type="button" id="next-page" value="'+_self.properties.messagesButtonMore+'">');
					}
				}
			}
		});
	}
	
	this.send_message = function(){
		message = _self.send_message_input_obj.val();
		id_user = _self.properties.idUser;
		$.ajax({
			url: _self.properties.siteUrl + _self.properties.messagesSendUrl,
			type: 'POST',
			data: {message: message, id_user: id_user},
			dataType : 'json',
			cache: false,
			success: function(resp){
				if(resp.content){
					_self.send_message_input_obj.val('');
					message = resp.content.message;
					content = '<dl class="pointer bold"><dt class="w650"><div class="addressbar"><font>'+resp.content.date_created+'</font><font> '+resp.content.output_name+'</font></div><div class="view">'+message+'</div></dt></dl>'
					_self.messages_content_obj.prepend(content).fadeIn();
					_self.properties.messagesCountPage = $('.addressbar:visible').length;
                                        error_object.show_error_block(resp.success, 'success');
				}
				if(resp.errors){
					error_object.show_error_block(resp.errors, 'error');
				}
			}
		});
	}

	_self.Init(optionArr);
}
