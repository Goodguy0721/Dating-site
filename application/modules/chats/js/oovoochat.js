function Oovoochat(options) {
    this.properties = {
        conferenceId: 'OOVOO_WEB_RTC',
        appToken: '',
        testMode: false,
        sessionToken: '',
        participantId: '',
        participantName: '',
        localVideoId: '',
        remoteVideoId: '',
        saveSessionUrl: '',
        completeChatId: 'complete-chat',
        pauseChatId: '',
        status: ''
    };
    
    var conference = null;
    
    var _self = this;
    
    this.init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        
        if (!_self.properties.sessionToken) {
            ooVoo.API.connect({
                token: _self.properties.appToken,
                isSandbox: _self.properties.testMode,
                userId: _self.properties.participantId,
                callbackUrl: site_url + _self.properties.saveSessionUrl
            });
        }
        else {
            ooVoo.API.init({
                userToken: _self.properties.sessionToken
            }, _self.onAPI_init);
        }
        
        $('#' + _self.properties.completeChatId).unbind('click').bind('click', function(e) {
            e.preventDefault();
            
            if (_self.conference) {
                _self.conference.disconnect();
            }
            
            $('#his_info_block').show();
            $('#chat_user_block').show();
        
            $('#'+_self.properties.pauseChatId).hide();
        });
    }
    
    this.onAPI_init = function(res) {
        _self.conference = ooVoo.API.Conference.init({ video: true, audio: true }, _self.onConference_init);
    }
        
    window.onStreamPublished = function(stream) {
        document.getElementById(_self.properties.localVideoId).src = URL.createObjectURL(stream.stream);
    }

    window.onParticipantLeft = function(evt) {
        if (evt.uid) {
            document.getElementById(_self.properties.remoteVideoId).src = '';
		
            $('#his_info_block').show();
            $('#chat_user_block').show();
        
            $('#'+_self.properties.pauseChatId).hide();
        }
    }
    
    window.onParticipantJoined = function(evt) {
        if (evt.stream && evt.uid != null) {
            document.getElementById(_self.properties.remoteVideoId).src = URL.createObjectURL(evt.stream);
            
            $('#his_info_block').hide();
            $('#chat_user_block').hide();
        }
    }
    
    window.onConferenceStateChanged = function(evt) {
        
    }
    
    window.onRemoteVideoStateChanged = function(evt) {
        switch (evt.state) {
            case 'PLAY':
                $('#his_info_block').hide();
                $('#chat_user_block').hide();
                break;
            
            case 'PAUSED':
                alert('Paused');
                break;
                
            case 'STOPPED':
                $('#his_info_block').show();
                $('#chat_user_block').show();
                break;
        }
    }  
    
    this.onConference_init = function(res) {
        if (!res.error) {
            //register to conference events
            _self.conference.onParticipantJoined = onParticipantJoined;
            _self.conference.onParticipantLeft = onParticipantLeft;
            _self.conference.onLocalStreamPublished = onStreamPublished;
            _self.conference.onConferenceStateChanged = onConferenceStateChanged;
            _self.conference.onRemoteVideoStateChanged = onRemoteVideoStateChanged;

            _self.conference.setConfig({
                videoResolution: ooVoo.API.VideoResolution["HIGH"],
                videoFrameRate: new Array(5, 15)
            }, function (res) {
                if (!res.error) {
                    _self.conference.join(_self.properties.conferenceId, 
                                    _self.properties.participantId, 
                                    _self.properties.sessionToken, 
                                    _self.properties.participantName, 
                                    function (result) { });
                }
            });
        }
    }

    _self.init(options);

	return this;
}

