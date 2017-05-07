function usersSettings(optionArr) {
	this.properties = {
        siteUrl: '',
				guest_view_profile_allow_id: '#guest_view_profile_allow',
				guest_view_profile_allow: {},
				guest_view_profile_limit_id: '#guest_view_profile_limit',
				guest_view_profile_limit: {},
				guest_view_profile_num_id: '#guest_view_profile_num',
				guest_view_profile_num: {},
        availableData: '',
        getChangeLocationForm: 'users/getChangeLocationForm',
        setChangeLocationForm: 'users/setChangeLocationForm',
        getAvailableActivation: 'users/getAvailableActivation/',
				dataChangeLocation: '[data-change=location]',
				changeLocationBlock: '#change-location-block',
				saveLocationBlock: '#save-location-block',
        errorObj: new Errors,
        contentObj: new loadingContent({
            loadBlockWidth: '400px',
            loadBlockLeftType: 'center',
            loadBlockTopType: 'top',
            loadBlockTopPoint: 100,
            closeBtnClass: 'w'
        })
	};

	var _self = this;

	var getObjects = function () {
		_self.properties.guest_view_profile_allow = $(_self.properties.guest_view_profile_allow_id);
		_self.properties.guest_view_profile_limit = $(_self.properties.guest_view_profile_limit_id);
		_self.properties.guest_view_profile_num = $(_self.properties.guest_view_profile_num_id);
	};

	var guestOptionsState = function() {
		if(_self.properties.guest_view_profile_allow.is(':checked')) {
			_self.properties.guest_view_profile_limit
					.attr('disabled', null);
		} else {
			_self.properties.guest_view_profile_limit
					.attr('disabled', 'disabled')
					.attr('checked', null);
		}
		if(_self.properties.guest_view_profile_limit.is(':checked')) {
			_self.properties.guest_view_profile_num
					.attr('disabled', null);
		} else {
			_self.properties.guest_view_profile_num
					.attr('disabled', 'disabled')
					.val(0);
		}
	};

	var bindEvents = function () {
		_self.properties.guest_view_profile_allow.on('change', function(){
			guestOptionsState();
		});
		_self.properties.guest_view_profile_limit.on('change', function(){
			guestOptionsState();
		});
	};

	this.Init = function (options) {
		_self.properties = $.extend(_self.properties, options);
        _self.init_controls();
		getObjects();
		bindEvents();
		guestOptionsState();
	};

    this.uninit = function () {
        $(document)
                .off('click', '.' + _self.properties.dataChangeLocation)
                .off('click', '.' + _self.properties.saveLocationBlock);
        return this;
    };

    this.init_controls = function () {
        $(document).off('click', _self.properties.dataChangeLocation).on('click', _self.properties.dataChangeLocation, function () {
            _self.changeLocation();
        }).off('click', _self.properties.saveLocationBlock).on('click', _self.properties.saveLocationBlock, function () {
            _self.saveLocation();
        });
    };

    this.changeLocation = function () {
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: _self.properties.siteUrl + _self.properties.getChangeLocationForm,
            success: function (content) {
                if (typeof (content) !== 'undefined') {
                    _self.properties.contentObj.show_load_block(content);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.error(errorThrown);
                }
            }
        });
    };

    this.saveLocation = function () {
        var post_data = {
                id_country: $(_self.properties.changeLocationBlock).find('[name=id_country]').val(),
                id_region: $(_self.properties.changeLocationBlock).find('[name=id_region]').val(),
                id_city: $(_self.properties.changeLocationBlock).find('[name=id_city]').val()
            };
        $.ajax({
            type: 'POST',
            data: post_data,
            dataType: 'JSON',
            url: _self.properties.siteUrl + _self.properties.setChangeLocationForm,
            success: function (data) {
                if (typeof (data.errors) != 'undefined' && data.errors != '') {
                    _self.properties.errorObj.show_error_block(data.errors, 'error');
                } else if (typeof (data.success) != 'undefined' && data.success != '') {
                    _self.properties.errorObj.show_error_block(data.success, 'success');
                    var locationName = $(_self.properties.changeLocationBlock).find('[name=region_name]').val() || data.select_region;
                    $(_self.properties.dataChangeLocation).html('<i class="fa fa-map-marker"></i>&nbsp;' + locationName)
                }
                _self.properties.contentObj.hide_load_block();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.error(errorThrown);
                }
            }
        });
    };

    this.availableActivation = function () {
        $.ajax({
            type: 'GET',
            dataType: 'JSON',
            url: _self.properties.siteUrl + _self.properties.getAvailableActivation,
            success: function (data) {
                if (!$.isEmptyObject(data) && !data.errors) {
                    data.unshift('<button type="button" class="close" data-action="close" data-cookie="available_activation" aria-hidden="true">&times;</button>');
                    _self.properties.errorObj.showStaticErrorsblock(data, 'info');
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
//                if (typeof (console) !== 'undefined') {
//                    console.error(errorThrown);
//                }
            }
        });
    };

	_self.Init(optionArr);
}
