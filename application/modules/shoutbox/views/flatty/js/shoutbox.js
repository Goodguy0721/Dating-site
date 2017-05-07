function Shoutbox(params){

	this.properties = {
		id: 'shoutbox',
		site_url: '',
		active_ajax_timeout: 3,
		inactive_ajax_timeout: 6,
		id_user: 0,
		max_id: 0,
		min_id: 0,
		count_newm: 0,
		msg_max_length: 150,
		user_name: '',
		position: 'right',
		get_messages_url: 'shoutbox/ajax_get_messages',
		post_message_url: 'shoutbox/ajax_post_message',
		new_msgs: {messages: {}, count_new: 0},
        shoutboxPanel: '#shoutbox-panel',
        shoutboxBlock: '#shoutbox_block',
        shoutboxBtn: '#shoutbox_btn',
        shoutboxClose: '#shoutbox-close',
        shoutboxPanelBottom: '#shoutbox_panel-bottom',
		shoutbox_msg_btns_id: 'shoutbox_msg_btns',
        shoutboxMobileBlock: '.shoutbox-mobile-block',
        bottomBtns: '#bottom-btns',
        toggleBlock: '.js-toggle-block',
		is_dom_init: false,
		load_old: false,
        is_opened: false
	}

	var _p = {
		shoutboxPanelObj: {},
		shoutboxBlockObj: {},
		shoutboxBtnObj: {},
		shoutboxCloseObj: {},
		shoutbox_obj: {},
		msg_textarea_obj: {},
		msg_chat_obj: {},
		msg_btns_obj: {},
		counter_new_messages_obj: {},
		btn_in_top_obj: {},
		counter_to: {},

		xhr_send_message: null,
	};

	this.init_vars = function(){
		_p.xhr_send_message = null;

		this.shoutbox_messages = {max_id: 0, min_id: 0,msg: []};
	}

	var _self = this;
	_self.init_vars();

	
	this.init = function(options){
		options = options || {};
		$.extend(true, _self.properties, options);
		
		if(!_self.properties.id_user){
			return this;
		}

		_p.initDomObjects();
		_self.shoutbox_messages = {max_id: _self.properties.max_id, min_id: _self.properties.min_id, msg: []};
		_p.renderMessages(_self.properties.new_msgs, 'init');
		_p.initMultiRequest();
		$('body').append('<div id="'+_self.properties.id+'_full_text" class="shoutbox_full_text" style="display:none;"><div>')
			.on('mouseleave', '#'+_self.properties.id+'_full_text', function(){$('#'+_self.properties.id+'_full_text').fadeOut(0);});

		_self.initScroll();
		return this;
	}
	

	this.initScroll = function(){
		try{
			_p.msg_chat_obj.slimScroll({railVisible: true, height:'250px', size:'5px', position:_self.properties.position, endScrollY: function(){_p.loadOldMessage()}});
		}catch(e){}
	}

	_p.loadOldMessage = function(){
		if (_self.properties.load_old) return;
		_self.properties.load_old = true;
		var min_id = _p.getMinId();
		if (min_id<2) return;
		
		$.ajax({
			type: 'POST',
			url: _self.properties.site_url + _self.properties.get_messages_url,
			data: {min_id: min_id},
			success: function(resp, textStatus, jqXHR){
				_p.setShoutboxMessages(resp);

				if(_self.properties.new_msgs.messages){
					delete(_self.properties.new_msgs.messages);
				}
				_p.renderMessages(_self.shoutbox_messages.msg, 'replace_old');
				_self.shoutbox_messages.loaded = 1;
				_self.properties.load_old = false;
				_self.initScroll();
			},
			error: function(jqXHR, textStatus, errorThrown){
				if(typeof(console)!=='undefined'){
					console.error(errorThrown);
				}
			},
			dataType: 'json',
			backend: 1
		});

		return this;
	}

	this.uninit = function(){
		if(_self.properties.is_dom_init){
			_p.msg_btns_obj.off('click', 'input[name="sendbtn"]');
			_p.msg_textarea_obj.off('keydown');
			_p.shoutbox_obj.hide();
		}
		_self.properties.new_msgs = {messages: {}, count_new: 0};
		_self.init_vars();
		return this;
	}

	this.die = function(){
		MultiRequest.disableAction('shoutbox_new_msgs');
		_self.uninit();
		$(_self.properties.shoutboxBlock).remove();
		return this;
	}
    
    this.showShoutbox = function () {
		_p.panel_obj.fadeIn();
		if (_p.message_window_state) {
			
			_p.msg_window_obj.fadeIn();
		} else {
			_p.msg_window_obj.hide();
		}
		$(document).trigger('im:show');
		return this;
	};

	this.sendMessage = function(){
		if(_p.xhr_send_message && _p.xhr_send_message.readyState != 4){
			return this;
		}

		var text = _p.trimStr(_p.msg_textarea_obj.val());
		if(!text){
			return this;
		}

		_p.xhr_send_message = $.ajax({
			type: 'POST',
			url: _self.properties.site_url + _self.properties.post_message_url,
			data: { text: text, max_id: _p.getMaxId(), id_user: _self.properties.id_user},
			success: function(resp, textStatus, jqXHR){
				if(!resp.errors.length){
					_p.msg_textarea_obj.val('');
					$('#'+_self.properties.id+'_msg_count').text(_self.properties.msg_max_length);
					if(resp.messages.msg.length){
						_p.setShoutboxMessages(resp.messages);	
						_p.renderMessages(resp.messages.msg, 'replace_my');
					}
				}else{
					error_object.show_error_block(resp.errors, 'error');
				}
				if(resp.notices && resp.notices.length){
					error_object.show_error_block(resp.notices, 'info');
				}
			},
			error: function(jqXHR, textStatus, errorThrown){

			},
			dataType: 'json',
			async: true,
			backend: 0
		});
	}


	this.updateNewMessages = function(data){
		_self.properties.new_msgs = {count_new: data.count_new, messages: {}};
		
		for(var i = 0; i < data.messages.length; i++){
			_p.getMessages();
		}
		_self.setCounterNewMessages();
		return this;
	}


	this.setCounterNewMessages = function(){
		if(_p.counter_to){
			clearTimeout(_p.counter_to);
		}
		
		if (_p.msg_chat_obj.scrollTop()<55){
			_p.counter_new_messages_obj.text('');
			_self.properties.count_newm = 0;
			_p.btn_in_top_obj.hide();
			return;
		}
		_p.counter_to = setTimeout(function(){_self.setCounterNewMessages();}, 500);
		return this;
	}


	_p.getMessages = function(){

		$.ajax({
			type: 'POST',
			url: _self.properties.site_url + _self.properties.get_messages_url,
			data: {max_id: _p.getMaxId()},
			success: function(resp, textStatus, jqXHR){
				_p.setShoutboxMessages(resp);

				if(_self.properties.new_msgs.messages){
					delete(_self.properties.new_msgs.messages);
				}
				_p.renderMessages(_self.shoutbox_messages.msg, 'replace');
			},
			error: function(jqXHR, textStatus, errorThrown){
				if(typeof(console)!=='undefined'){
					console.error(errorThrown);
				}
			},
			dataType: 'json',
			backend: 1
		});

		return this;
	}


	_p.setShoutboxMessages = function(data){
		if(_self.shoutbox_messages){
			if(_self.shoutbox_messages.max_id < data.max_id){
				_self.shoutbox_messages.max_id = data.max_id;
			}
			if(_self.shoutbox_messages.min_id > data.min_id){
				_self.shoutbox_messages.min_id = data.min_id;
			}
		}else{
			_self.shoutbox_messages = {
				min_id: data.min_id,
				max_id: data.max_id,
				msg: []
			}
		}
		for(var i = 0; i < data.msg.length; i++){
			var msg_id = parseInt(data.msg[i].id);
			_self.shoutbox_messages.msg[msg_id] = data.msg[i];
		}
	}


	_p.renderMessages = function( messages, type){
		var html = '', replace_from_msg_obj = null, replace_to_msg_obj = null, user_name;
		if (type == 'replace_old') {
			messages.reverse(); 
		}
		for(var i in messages) if(messages.hasOwnProperty(i)){
			if(!$('#shoutbox_msg_'+messages[i].id).size()){
				var user_name = '';
				if (messages[i].user_info.nickname) {
					user_name = messages[i].user_info.nickname;
				} else {
					user_name = messages[i].user_info.output_name;
				}
				var elem = $('<div id="shoutbox_msg_'+messages[i].id+'" data-msg-id="'+messages[i].id+'" class="shoutbox-contact box-sizing"><div class="shoutbox-contact__image"><a href="'+messages[i].user_info.link+'"><img src="'+messages[i].user_info.media.user_logo.thumbs.small+'"></a></div><div class="shoutbox-contact__info"><div class="shoutbox_msg_header"><span class="shoutbox_user_name">'+user_name+'</span><span class="date">, '+messages[i].date_str+'</span></div><div class="shoutbox_msg_body">'+messages[i].message+'</div></div></div>');
				if (type == 'replace_old') {
					$(elem).appendTo(_p.msg_chat_obj);
				} else {
					$(elem).prependTo(_p.msg_chat_obj);				
				}
				if ((type != 'replace_old') && (type != 'init') && (type != 'replace_my')) {
                     $(elem).addClass('new-msg').animate({backgroundColor: "#FFFFFF"},{duration:2000,complete:function(){$(elem).removeClass('new-msg').removeAttr('style')}});
					_p.msg_chat_obj.scrollTop(_p.msg_chat_obj.scrollTop()+$(elem).height()+5);
					_self.properties.count_newm = _self.properties.count_newm + 1;
					if (_self.properties.count_newm!=0) {
						_p.counter_new_messages_obj.text('('+_self.properties.count_newm+')');
                        
					}
					_self.initScroll();
				}
				
			}
		}
		
		if ((type != 'replace_old') && (type != 'init') && (type != 'replace_my')) {
			if (_p.msg_chat_obj.scrollTop()>55){
				_p.btn_in_top_obj.show();
				setTimeout(function(){_p.btn_in_top_obj.hide();}, 6000);
			} else {
				_p.msg_chat_obj.scrollTop(0);
				_p.counter_new_messages_obj.text('');
				_self.properties.count_newm = 0;
			}
		}else if (type == 'replace_my') {
			_p.msg_chat_obj.scrollTop(0);
			_p.counter_new_messages_obj.text('');
			_self.properties.count_newm = 0;
			_self.initScroll();
		}
        _self.updateCountMessages(_self.properties.count_newm);
		return this;
	}

	_p.initDomObjects = function(){
        _p.shoutboxPanelObj = $(_self.properties.shoutboxPanel);
        _p.shoutboxBlockObj = $(_self.properties.shoutboxBlock);
        _p.shoutboxBtnObj = $(_self.properties.shoutboxBtn);
        _p.shoutboxCloseObj = $(_self.properties.shoutboxClose);
		_p.shoutbox_obj = $('#'+_self.properties.id);
		_p.msg_textarea_obj = _p.shoutbox_obj.find('.shoutbox-bottom textarea');
		_p.msg_chat_obj = _p.shoutbox_obj.find('.shoutbox-content .shoutbox-scroller');
		_p.msg_btns_obj = $('#'+_self.properties.shoutbox_msg_btns_id);
		_p.counter_new_messages_obj = $('#shoutbox_counter_nm');
		_p.btn_in_top_obj = _p.shoutbox_obj.find('.shoutbox-in-top');
        
        _p.shoutboxPanelObj.off('click', _self.properties.shoutboxClose).on('click', _self.properties.shoutboxClose, function(){
            _self.hideShoutbox();
		});
        
        _p.shoutboxPanelObj.off('click', _self.properties.shoutboxBtn).on('click', _self.properties.shoutboxBtn, function(){
            _self.showShoutbox();
		});
        
		_p.msg_btns_obj.off('click', 'input[name="sendbtn"]').on('click', 'input[name="sendbtn"]', function(){
			_self.sendMessage();
			_p.msg_textarea_obj.focus();
		});
		_p.btn_in_top_obj.off('click').on('click', function(){
			_p.msg_chat_obj.scrollTop(0);
			_p.counter_new_messages_obj.text('');
			_self.properties.count_newm = 0;
			_p.btn_in_top_obj.hide();
			$('.slimScrollBar').css('top',0);
		});
		_p.msg_chat_obj.off('click', '.shoutbox_user_name').on('click', '.shoutbox_user_name', function(){
			_p.msg_textarea_obj.val(_p.msg_textarea_obj.val() + $(this).text() + ', ');
			_p.msg_textarea_obj.trigger('keyup');
		});
		_p.msg_textarea_obj.off('keydown').on('keydown', function(e){
			if(e.ctrlKey && e.keyCode === 13){
				_self.sendMessage();
			}
		}).off('keyup blur').on('keyup blur', function(){
			var msg_length = $(this).val().length;
			if (msg_length > _self.properties.msg_max_length) {
				$(this).val($(this).val().substring(0, _self.properties.msg_max_length)).scrollTop(1000);
			}
			msg_length = $(this).val().length;
			$('#'+_self.properties.id+'_msg_count').text(_self.properties.msg_max_length - msg_length);
		});
        
        $(document).off('click', '[data-id=shoutbox_panel-bottom]').on('click', '[data-id=shoutbox_panel-bottom]', function () {
            _self.createMobileBlock();
		});

		_p.shoutbox_obj.show();

		_self.properties.is_dom_init = true;

		return this;
	}



	_p.initMultiRequest = function(){
		var actions = [
			{
				gid: 'shoutbox_new_msgs',
				params: {module: 'shoutbox', model: 'Shoutbox_model', method: 'check_new_messages', 'max_id': _p.getMaxId},
				paramsFunc: function(){return {}},
				callback: function(resp){
					if(resp){
						_self.updateNewMessages(resp);
					}
				},
				period: 3,
				status: 1
			}
		];

		MultiRequest.initActions(actions);
		return this;
	}
	
	_p.getMaxId = function(){
		var  max_id = 0;
		if(_self.shoutbox_messages){
			max_id = _self.shoutbox_messages.max_id;
		}
		return max_id;
	}
	_p.getMinId = function(){
		var  min_id = 0;
		if(_self.shoutbox_messages){
			min_id = _self.shoutbox_messages.min_id;
		}
		return min_id;
	}

	_p.trimStr = function(s) {
		s = s.replace( /^\s+/g, '');
		return s.replace( /\s+$/g, '');
	}
    
    this.showShoutbox = function () {
        _p.shoutboxBtnObj.hide();
        _p.shoutboxBlockObj.fadeIn();
        //$("body").addClass("js-noscroll");
        return this;
    };
    
    this.hideShoutbox = function () {
        _p.shoutboxBlockObj.hide();
        _p.shoutboxBtnObj.fadeIn();
        //$("body").removeClass("js-noscroll");
        return this;
    };
    
    this.createMobileBlock = function () {
        width = $(window).width();
        
        if (width < 768) {
            if (!$(_self.properties.shoutboxPanelBottom).find('div').length) {
                _p.shoutboxPanelObj.appendTo(_self.properties.shoutboxPanelBottom);
                $(_self.properties.shoutboxMobileBlock).parent('div').show();
                _self.showShoutbox();
                //$("body").removeClass("js-noscroll");
            } else {
                if ($(_self.properties.shoutboxMobileBlock).parent('div').is(':hidden')) {
                    $(_self.properties.toggleBlock).hide();
                    $(_self.properties.shoutboxMobileBlock).parent('div').show();
                } else {
                    $(_self.properties.toggleBlock).hide();
                }                
            }
        } else {
            if ($(_self.properties.shoutboxPanelBottom).find('div').length) {
                _p.shoutboxPanelObj.prependTo(_self.properties.bottomBtns);
                _self.hideShoutbox();
            }
        }
        return this;
    };
    
    this.updateCountMessages = function (count) {
        if (count > 0) {
            $('.mobile-top-menu').find('[data-mblock-id=shoutbox_panel-bottom]').html('<span class="badge">' + count + '</span>');
        } else {
            $('.mobile-top-menu').find('[data-mblock-id=shoutbox_panel-bottom]').html('');
        }
        return this;
    };
    
    this.scrollToShoutbox = function () {
        var width = $(window).width();
        if (width  < 768) {
            $('html, body').animate({scrollTop: $(_self.properties.shoutboxMobileBlock).offset().top}, 800);
        }  
        return this;        
    }


	_self.init(params);

	return this;
};
