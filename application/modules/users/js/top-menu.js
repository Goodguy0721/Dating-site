function topMenu(optionArr){

	this.properties = {
		siteUrl: '/',
		parent: '#users-top-menu',
		summands: '.summand',
		sum: '.sum',
		noNotifications: 'no-notifications',
		hide: 'hide-always',
                summandsParent: 'li:first',
                blockMore: '.menu-alerts-more',
                
                sidebarParent: '#slidemenu',
                sidebarSum: '.sidebar-sum',
                sidebarSumVal: 0
	};

	var _self = this;

	var init_multi_request = function() {
		if(typeof MultiRequest === 'undefined'){
			return false;
		}
		MultiRequest.initAction({
			gid: 'notifications-sum',
			params: {},
			paramsFunc: function(){return {};},
			callback: function(){
                            _self.properties.sidebarSumVal = 0;
                            $(_self.properties.parent).each(function(){
				_self.init_objects(this);
				_self.update_num(this);
                            });
                            _self.update_sidebar_sum();
			},
			period: 3,
			status: 0
		});

		if(id_user){
			MultiRequest.enableAction('notifications-sum');
		}
		$(document).on('users:login', function(){
			MultiRequest.enableAction('notifications-sum');
		}).on('users:logout, session:guest', function(){
			MultiRequest.disableAction('notifications-sum');
		});

	};

	this.Init = function(options){
            _self.properties = $.extend(_self.properties, options);
            $(_self.properties.parent).each(function(){	
		_self.init_objects(this);
		_self.update_num(this);
		init_multi_request();
            });
            _self.update_sidebar_sum();
	};

	this.init_objects = function(parent){
		_self.summands = $(_self.properties.summands, parent);
		_self.sum = $(_self.properties.sum, parent);
                _self.sidebarSummands = $(_self.properties.summands, _self.properties.sidebarParent);
	};

	this.update_num = function(parent) {
		var sum = get_sum();
		_self.sum.html(sum ? sum : '');
		if(!sum) {
                        $(parent).addClass(_self.properties.noNotifications);
		} else {
                        $(parent).removeClass(_self.properties.noNotifications);
		}
	};
        
        this.update_sidebar_sum = function() {
            if(typeof _self.sidebarSummands !== 'undefined') {
                var count = 0;
                _self.sidebarSummands.each(function(){
                    count = parseInt($(this).html());
                    _self.properties.sidebarSumVal += count;
                    if(count) {
                            $(this).removeClass(_self.properties.hide);
                    } else {
                            $(this).addClass(_self.properties.hide);
                    }
                });

                if(_self.properties.sidebarSumVal) {
                        $(_self.properties.sidebarSum).removeClass(_self.properties.hide).html(_self.properties.sidebarSumVal);
                } else {
                        $(_self.properties.sidebarSum).addClass(_self.properties.hide);
                }                
            }
        }

	var get_sum = function() {
		var sum = 0;
		var count = 0;
		_self.summands.each(function(){
			count = parseInt($(this).html());
			var parent = $(this).parents(_self.properties.summandsParent);
			if(count) {
				sum += count;
				parent.removeClass(_self.properties.hide);
			} else {
				parent.addClass(_self.properties.hide);
			}
		});
		return sum;
	};

	_self.Init(optionArr);
}