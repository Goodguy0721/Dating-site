function registerFormInput(optionArr){
	this.properties = {
		siteUrl: '',
                send_form_data: 'incomplete_signup/ajax_get_register_form_data/',
                timeout: 15000,
                intervalid: ''
	}
        
        this.fields = {
                user_type: '',
                looking_user_type: '',
                email: '',
                nickname: '',
                birth_date: '',
                id_country: '',
                id_region: '',
                id_city: '',
                fname: '',
                sname: '',
                user_logo: '',
        }

        var _self = this;
        
	this.Init = function(options){
                _self.properties = $.extend(_self.properties, options);
        
		_self.properties.intervalid = setInterval(function() {
                        _self.fields.email = $("input[name='email']").val();
                        if(_self.IsEmail(_self.fields.email)) {
                                _self.fields.user_type = $("select[name='user_type']").val();
                                _self.fields.looking_user_type = $("select[name='looking_user_type']").val();
                                _self.fields.nickname = $("input[name='nickname']").val();
                                _self.fields.birth_date = $("input[name='birth_date']").val();
                                _self.fields.id_country = $("input[name='id_country']").val();
                                _self.fields.id_region = $("input[name='id_region']").val();
                                _self.fields.id_city = $("input[name='id_city']").val();
                                
                                _self.fields.fname = $("input[name='fname']").val();
                                _self.fields.sname = $("input[name='sname']").val();
                                _self.fields.user_logo = $("input[name='user_logo']").val();
                                
                                var send_data = {'data_fields': _self.fields};

                                $.ajax({
                                        url: _self.properties.siteUrl+_self.properties.send_form_data,
                                        type: 'POST',
                                        data: send_data,
                                        cache: false,
                                        success: function(data){
                                            
                                        }
                                });
                        }
			return false;
		},_self.properties.timeout);
	}
        
        this.IsEmail = function(email) {
            var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
            return regex.test(email);
        }

	_self.Init(optionArr);
}
