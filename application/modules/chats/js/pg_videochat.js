function PG_videochat(params) {

	this.properties = {
		site_url: 				'',
		settings: 				[],
		id_chat: 				0,
		id_inviter_user: 		0,
		id_invited_user: 		0,
		inviter_peer_id: 		'',
		invited_peer_id: 		'',
		video_id_you: 			'its_me',
		video_id_him: 			'its_him',
		video_you: 				null,
		video_him: 				null,
		pauseVideo: 			'pauseVideo',
		pauseAudio: 			'pauseAudio',
		pauseChat: 				'pauseChat',
		completeChat: 			'completeChat',
		messagesChat: 			'messagesChat',
		is_inviter: 			false,
		is_pauseVideo: 			false,
		is_pauseAudio: 			false,
		is_pauseChat: 			false,
		is_pauseHisChat: 		false,
		is_initMyCam: 			false,
		status: 				'send',
		timeout_set_status: 	null,
		timeout_set_status: 	null,
		timeout_change_status: 	null,
		xhr_check_status:		null,
		him_streem:				null,
		
		waiting_lang:			'Waiting for other people...',
		pause_lang:				'Pause',
		complete_lang:			'Complete',
		close_alert_text:		'You are going to finish chat session. Are you sure?',
		error_support:			'Browser is not supported',
		connect_now:			'Connect now',
	
		msg_input_obj: 			'videochatMessage',
		inviter_photo: 			'',
		invited_photo: 			'',
		my_user_id: 			0,
		message_max_id:			0,
		is_sending:				false,
		getMassages:			false,
		
		change_status: 			'chats/ajax_change_status/',
		get_messages: 			'chats/ajax_get_messages/',
		send_messages: 			'chats/ajax_send_message/',

		start_chat_id: 			'start-chat-link',
		close_chat_id: 			'close-chat',
		chat_block: 			'start-chat-block',
                try_connect:                    false,
                chat_exists:                    null,
	};
	
	var _self = this;
	var peer = null;
	
	this.init = function (options) {
		options = options || {};
		$.extend(true, _self.properties, options);
		if (!_self.properties.id_inviter_user || !_self.properties.id_invited_user) {
			return this;
		}
		
		_self.properties.video_you = $('#'+_self.properties.video_id_you);
		_self.properties.video_him = $('#'+_self.properties.video_id_him);
		_self.properties.close = false;
		
		$("#"+_self.properties.start_chat_id).on('click', function(){
                    $("#col-chat").animate({ width: "80%" }, 300 );
                    $("#"+_self.properties.chat_block).show();
                    _self.initScroll();
                });

                $("#"+_self.properties.close_chat_id).on('click', function(){
                    $("#col-chat").animate({width: "100%" }, 300 );
                    $("#"+_self.properties.chat_block).hide();
                });
		
		if (_self.properties.is_inviter){
			_self.properties.my_user_id = _self.properties.id_inviter_user;
		} else{
			_self.properties.my_user_id = _self.properties.id_invited_user;
		}
		_self.initStep1();

		return this;
	};
	
	this.initStep1 = function () {
		navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia;
		
		if (!navigator.getUserMedia) {
			$('#my_info_block').html('<div style="padding:10px; font-size:20px">'+_self.properties.error_support+'</div>');
			$('#my_info_block').show();
			return;
		}

		$('#my_info_block').show();

		$('#'+_self.properties.completeChat).off('click').on('click', function () {
			alerts.show({
				text: _self.properties.close_alert_text,
				type: 'confirm',
				ok_callback: function () {
					_self.properties.close = true;
					_self.initClose();
				}
			});
		});
		
		_self.initMyCam();
		
		return this;
	};

	this.initMyCam = function () {
		navigator.getUserMedia({audio: true,video: true}, _self.getStream, function(e) { console.log(e.name); });
	};

	this.getStream = function (stream) {

		window.localStream = stream;
		$('#my_info_block').hide();
		$('#'+_self.properties.video_id_you).prop('src', URL.createObjectURL(stream));

		_self.getPeer();
	};

	this.getPeer = function () {
		peer = new Peer({
			host: 'video.pilotgroup.net',
			port: 9001,
			secure: false,
			debug: 0,
			logFunction: false,
            config: {'iceServers': [
                { url: 'stun:5.9.62.115:3478' },
                /*{ url: 'turn:user@5.9.62.115:3478', credential: '' }*/
            ]}
		});
		
		peer.off('open').on('open', function(){
			if (_self.properties.is_inviter){
				_self.properties.inviter_peer_id = peer.id;
			} else{
				_self.properties.invited_peer_id = peer.id;
			}

			$('#his_info_block_text').html(_self.properties.waiting_lang);
			_self.properties.status='connect';

			_self.initStep2();

			$('#his_info_block').show();
		});
		
	};
	
	this.initStep2 = function () {
		
		_self.properties.is_initMyCam = true;
		if (_self.properties.is_inviter){
			var incomming_peer_id = _self.properties.invited_peer_id;
		} else{
			var incomming_peer_id = _self.properties.inviter_peer_id;
		}
		
		$('#'+_self.properties.pauseVideo).off('click').on('click', function () {
			_self.initPauseVideo();
		});
		$('#'+_self.properties.pauseAudio).off('click').on('click', function () {
			_self.initPauseAudio();
		});
		$('#'+_self.properties.pauseVideo).show();
		$('#'+_self.properties.pauseAudio).show();
		
		_self.initChangeStatus();
		
		if (_self.properties.is_inviter){
			_self.initCheckStatus();
		}
		
		console.log('initStep2')
		
		peer.off('call').on('call', function(call){
			call.answer(window.localStream);
			console.log('initHisCam')
			_self.initHisCam(call);
		});
		
		//peer.emit('call');

		peer.off('error').on('error', function(err){
			if (window.existingCall) {
				window.existingCall.close();
			}
			
			console.log('err: ', err)
			
			$('#'+_self.properties.video_id_him).hide();
			if (_self.properties.status!='stop') {
				
				_self.properties.status = 'send';
				
                $('#chat_user_block').show();
				if (_self.properties.is_inviter){
					_self.initCheckStatus();
				}
			}

		});

		peer.off('connection').on('connection', function(conn) {
			conn.off('data').on('data', function(data) {
				_self.appendMessage('peer', data);
				conn.close();
			});
		});

                _self.tryConnect();
                
		if (_self.properties.is_inviter){                  
			$(_self).on('send', function(o, msg) {
				var conn = peer.connect(_self.properties.invited_peer_id);
				conn.on('open', function(){
					conn.send(msg);
				});
			});
		} else{
			$(_self).on('send', function(o, msg) {
				var conn = peer.connect(_self.properties.inviter_peer_id);
				conn.on('open', function() {
					conn.send(msg);					
				});
			});
		}

		_self.addMessageTextChat();
		
		return this;
	};
        
	this.initHisCam = function (call) {
		if (_self.properties.status=='stop') {
			return false;
		}
		_self.properties.status='connect';
		
        call = call || null;
		if (!call){
			if (_self.properties.is_inviter){
				var call = peer.call(_self.properties.invited_peer_id, window.localStream);
			} else{
				var call = peer.call(_self.properties.inviter_peer_id, window.localStream);
			}
		}
		
		if (window.existingCall) {
			window.existingCall.close();
		}

		// Wait for stream on the call, then set peer video display
		call.on('stream', function(stream){
			_self.properties.him_streem = stream;
			$('#'+_self.properties.video_id_him).prop('src', URL.createObjectURL(stream));
			$('#'+_self.properties.video_id_him).show();
			$('#chat_user_block').hide();
			$('#his_info_block').hide();
			_self.properties.status = 'start';
			
            $('#'+_self.properties.pauseChat).show();
			$('#'+_self.properties.pauseChat).off('click').on('click', function () {
				_self.initPauseAudio();
				_self.initPauseVideo();
				_self.initPauseChat();
			});
		});

		// UI stuff
		window.existingCall = call;
		
	}

			$('#'+_self.properties.video_id_him).prop('src', URL.createObjectURL(stream));
			$('#'+_self.properties.video_id_him).show();
			$('#chat_user_block').hide();
			$('#his_info_block').hide();
			_self.properties.status = 'start';
			
            $('#'+_self.properties.pauseChat).show();
			$('#'+_self.properties.pauseChat).off('click').on('click', function () {
				_self.initPauseAudio();
				_self.initPauseVideo();
				_self.initPauseChat();
			});
		});

		// UI stuff
		window.existingCall = call;
		
	}

	this.addMessageTextChat = function () {
		$('#'+_self.properties.msg_input_obj).off('keydown').on('keydown', function (e) {
			if (e.keyCode === 13) {
				var msg = $('#'+_self.properties.msg_input_obj).val();
				if (msg != ''){
					_self.appendMessage('send', msg);
					$(_self).trigger('send', msg);
                                        
                                        $('#'+_self.properties.msg_input_obj).val('');
				}
			}
		});
	};
	
	this.appendMessage = function (s, msg) {
		var style = "right";
		var photo = _self.properties.invited_photo;

		if (s == 'peer'){
			style = "left";
			if (!_self.properties.is_inviter){
				var photo = _self.properties.inviter_photo;
			}
		} else {
			if (_self.properties.is_inviter){
				var photo = _self.properties.inviter_photo;
			}
		}
		
		var html =
			'<div class="vc-message vc-message--' + style + '">' +
			'<img src="' + photo + '" class="f' + style + '"/>' +
			'<div class="message">' + msg +'</div></div>';
		
		$('#'+_self.properties.messagesChat).append(html);

		var height = $('#messagesChat')[0].scrollHeight;
		$('.message-scroller').scrollTop(height);
	};
	
	this.initPauseVideo = function () {
		window.localStream.getVideoTracks().forEach(function (track) {
			track.enabled = _self.properties.is_pauseVideo;
		});
		$('#'+_self.properties.pauseVideo).toggleClass('fa-video-camera fa-eye-slash');
		_self.properties.is_pauseVideo = !_self.properties.is_pauseVideo;
	};
	
	this.initPauseAudio = function () {
		window.localStream.getAudioTracks().forEach(function (track) {
			track.enabled = _self.properties.is_pauseAudio;
		});
		$('#'+_self.properties.pauseAudio).toggleClass('fa-microphone fa-microphone-slash');
		_self.properties.is_pauseAudio = !_self.properties.is_pauseAudio;
	}
	
	this.initPauseChat = function () {
		if (_self.properties.is_pauseChat){
			_self.properties.status = 'start';
			_self.properties.video_him.muted = false;
			$('#'+_self.properties.video_id_him).show();
			$('#his_info_block').hide();
			$('#chat_user_block').hide();
		} else{
			_self.properties.status = 'pause';
			_self.properties.video_him.muted = true;
			$('#'+_self.properties.video_id_him).hide();
			$('#his_info_block_text').html(_self.properties.pause_lang);
			$('#his_info_block').show();
			
			$('#chat_user_block').show();
		}
		$('#'+_self.properties.pauseChat).toggleClass('fa-pause fa-play');
		_self.properties.is_pauseChat = !_self.properties.is_pauseChat;
	}
	
	this.initHisPauseChat = function () {
		if (_self.properties.is_pauseHisChat){
			_self.properties.video_him.muted = false;
			$('#'+_self.properties.video_id_him).show();
			$('#his_info_block').hide();
			$('#chat_user_block').hide();
		} else{
			_self.properties.video_him.muted = true;
			$('#'+_self.properties.video_id_him).hide();
			$('#his_info_block_text').html(_self.properties.pause_lang);
			$('#his_info_block').show();
			$('#chat_user_block').show();
		}
		_self.properties.is_pauseHisChat = !_self.properties.is_pauseHisChat;
	}
	
	this.initClose = function () {
		if (window.existingCall) {
			window.existingCall.close();
		}
		if (!_self.properties.is_initMyCam){
			_self.initChangeStatus(true);
		}
		$('#'+_self.properties.video_id_him).remove();
		_self.properties.status = 'stop';
		
        $('#his_info_block_text').html(_self.properties.complete_lang);
		$('#his_info_block').show();
		$('#chat_user_block').show();
		$('#'+_self.properties.pauseChat).hide();
		return false;
	}
	
	this.initCheckStatus = function () {
		if (_self.properties.timeout_set_status) {
			clearTimeout(_self.properties.timeout_set_status);
		}
		var incomming_peer_id = _self.properties.invited_peer_id;
		console.log(_self.properties.status)
		if (_self.properties.invited_peer_id!='' && _self.properties.status=='send'){
			_self.initHisCam();
		} else if (_self.properties.invited_peer_id!='' && _self.properties.status!='stop' && _self.properties.status!='connect'){
			_self.properties.timeout_set_status = setTimeout(function () {
				_self.initCheckStatus();
			}, 2000);
		}
		return false;
	}
	
	this.initChangeStatus = function (last) {
		last = last || false;
		not_equal = false;
		if (_self.properties.timeout_change_status) {
			clearTimeout(_self.properties.timeout_change_status);
		}
		$.ajax({
			type: 'POST',
			url: _self.properties.site_url + _self.properties.change_status+_self.properties.id_chat,
			data: {status: _self.properties.status, inviter_peer_id: _self.properties.inviter_peer_id, invited_peer_id: _self.properties.invited_peer_id},
			success: function (resp, textStatus, jqXHR) {
				if (resp.id && !last) {
					if (_self.properties.status!='stop') {
						if (_self.properties.inviter_peer_id != resp.inviter_peer_id){
							_self.properties.inviter_peer_id = resp.inviter_peer_id;
							not_equal = true;
						}
						if (_self.properties.invited_peer_id != resp.invited_peer_id){
							_self.properties.invited_peer_id = resp.invited_peer_id;
							not_equal = true;
						}

						if (not_equal) {                                                    
                                                    var dataConnection = peer.connect(_self.properties.invited_peer_id);

                                                    dataConnection.on('open', function() { 
                                                        _self.properties.chat_exists = true;
                                                        dataConnection.close();
                                                    });

                                                    dataConnection.on('error', function(err) { 
                                                        _self.properties.chat_exists = false;
                                                        dataConnection.close();
                                                    });
                                                }

						if (not_equal || _self.properties.chat_exists) {
                                                        if(_self.properties.chat_exists) {
                                                            _self.properties.chat_exists = false;
                                                        }
                                                    
							if (window.existingCall) {
								window.existingCall.close();
							}
							
							$('#'+_self.properties.video_id_him).hide();
							$('#chat_user_block').show();
							_self.properties.status = 'send';
							
                            $('#his_info_block_text').html(_self.properties.waiting_lang);
							$('#his_info_block').show();
							$('#chat_user_block').show();
							if (_self.properties.is_inviter){
								_self.initCheckStatus();
							}
						}
					}
				}
				if (resp.errors) {
					error_object.show_error_block(resp.errors, 'error');
				}
				if (((_self.properties.status == 'start' || _self.properties.status == 'send' || _self.properties.status == 'pause') && resp.status=='paused' && !_self.properties.is_pauseHisChat) || ((_self.properties.status == 'start' || _self.properties.status == 'send' || _self.properties.status == 'pause') && resp.status!='paused' && resp.status!='completed' && _self.properties.is_pauseHisChat)){
					_self.initHisPauseChat();
				}
				if (resp.status=='completed'){
					_self.initClose();
					
                    if (!last){
						_self.properties.timeout_change_status = setTimeout(function () {
							_self.initChangeStatus(true);
						}, 2000);
					}
				} else{
					_self.properties.timeout_change_status = setTimeout(function () {
						_self.initChangeStatus();
					}, 2000);
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				if (typeof (console) !== 'undefined') {
					console.error(errorThrown);
				}
			},
			dataType: 'json',
			backend: 1
		});
		return false;
	}
	
	this.initScroll = function () {
		var height = $('#messagesChat')[0].scrollHeight;
		$('.message-scroller').scrollTop(height);
	}
	
	_self.init(params);

	return this;
}
