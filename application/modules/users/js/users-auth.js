function UsersAuth(optionArr) { 'use strict';
    this.properties = {
        siteUrl: '/',
        loginBtnId: 'ajax_login_link',
        content : null,
    }

    var _self = this;

	this.Init = function(options){
		_self.properties = $.extend(_self.properties, options);

        $('#' + _self.properties.loginBtnId).unbind('click').click(function(e) {
            e.preventDefault(e);
            _self.login();
        });

        $('#ajax_register_link').unbind('click').click(function(e) {
            e.preventDefault(e);
            _self.register();
        });
    }

    this.uninit = function(){
		return this;
	};

    this.login = function() {
        $.ajax({
            url: _self.properties.siteUrl + 'users/ajax_login_form',
            cache: false,
            dataType: 'html',
            success: function(data){
                if (_self.properties.content == null) {
                    _self.properties.content = new loadingContent({loadBlockWidth: '500px', closeBtnClass: 'w', loadBlockTopType: 'bottom', loadBlockTopPoint: 20, blockBody: true, showAfterImagesLoad: false});
                }
                _self.properties.content.show_load_block(data);
            }
        });
    }

    this.register = function() {
        $.ajax({
            url: _self.properties.siteUrl + 'users/ajax_login_form/register',
            cache: false,
            dataType: 'html',
            success: function(data){
                _self.properties.content = new loadingContent({
                  loadBlockWidth: 'auto',
                  closeBtnClass: 'w',
                  otherClass: 'register-popup', 
                  loadBlockTopType: 'bottom',
                  loadBlockTopPoint: 20,
                   blockBody: true,
                   showAfterImagesLoad: false
                });
                _self.properties.content.show_load_block(data);
            }
        });
    }

    _self.Init(optionArr);

	return this;
}
