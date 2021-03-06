function favourites(optionArr) {
	this.properties = {
		siteUrl: '/',
		addUserButton: '.add_to_fav',
		removeUserButton: '.remove_from_fav',
		addUserUrl: 'favourites/ajax_add/',
		removeUserUrl: 'favourites/ajax_remove/',
		favId: '#fav_',
		toggle: true
	};

	var _self = this;

	this.Init = function(options) {
		_self.properties = $.extend(_self.properties, options);
		_self.init_objects();
		_self.init_controls();
	};

	this.uninit = function() {
		_self.add_user_btn.off('click');
		_self.remove_user_btn.off('click');
	};

	this.init_objects = function() {
		_self.add_user_btn = $(_self.properties.addUserButton);
		_self.remove_user_btn = $(_self.properties.removeUserButton);
	};

	this.init_controls = function() {
		_self.add_user_btn.off('click').on('click', function() {
			_self.add_user($(this).data('userid'));
			return false;
		});
		_self.remove_user_btn.off('click').on('click', function() {
			_self.remove_user($(this).data('userid'));
			return false;
		});
	};

	this.setBtn = function(id, type) {
		if('add' === type) {
			$(id + ' ' + _self.properties.addUserButton).show();
			$(id + ' ' + _self.properties.removeUserButton).hide();
		} else if('remove' === type) {
			$(id + ' ' + _self.properties.addUserButton).hide();
			$(id + ' ' + _self.properties.removeUserButton).show();
		}
	};

	this.add_user = function(userId) {
		_request(_self.properties.addUserUrl, userId, function() {
			if(_self.properties.toggle) {
				_self.setBtn(_self.properties.favId + userId, 'remove');
			} else {
				$(_self.properties.favId + userId).remove();
			}
		});
	};

	this.remove_user = function(userId) {
		_request(_self.properties.removeUserUrl, userId, function() {
			if(_self.properties.toggle) {
				_self.setBtn(_self.properties.favId + userId, 'add');
			} else {
				$(_self.properties.favId + userId).remove();
			}
		});
	};

	var _request = function(url, userId, successCb) {
		$.ajax({
			url: _self.properties.siteUrl + url + userId,
			type: 'GET',
			dataType: 'json',
			cache: false,
			success: function(data) {
				if (data.errors) {
					error_object.show_error_block(data.errors, 'error');
				} else {
					successCb();
					error_object.show_error_block(data.success, 'success');
				}
			}
		});
	};

	_self.Init(optionArr);
}
