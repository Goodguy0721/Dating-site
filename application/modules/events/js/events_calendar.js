"use strict";
function eventsCalendar(optionArr) {
	this.properties = {
		siteUrl: '/',
                current_month: 0,
                calendar_block_class: 'events-list', 
                btn_prev_month: 'btn_prev_month',
                btn_curr_month: 'btn_curr_month',
                btn_next_month: 'btn_next_month',
                change_month_url: 'events/ajaxChangeMonth',
                searchType: '',
	};
        
        var _self = this;
        
        this.Init = function(options) {
		_self.properties = $.extend(_self.properties, options);
		_self.init_controls();
	};
        
        this.init_controls = function() {
                $('#' + _self.properties.btn_prev_month).off('click').on('click', function(){
                    _self.prevMonth();
                });
                $('#' + _self.properties.btn_curr_month).off('click').on('click', function(){
                    _self.currMonth();
                });
                $('#' + _self.properties.btn_next_month).off('click').on('click', function(){
                    _self.nextMonth();
                });
        }
        
        this.prevMonth = function() {
            _self.properties.current_month--;
            _self.changeMonth();
        }
        
        this.currMonth = function() {
            _self.properties.current_month = 0;
            _self.changeMonth();
        }
        
        this.nextMonth = function() {
            _self.properties.current_month++;
            _self.changeMonth();

        }
        
        this.changeMonth = function() {
            $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    data: {'month': _self.properties.current_month, 'search_type': _self.properties.searchType},
                    url: _self.properties.siteUrl + _self.properties.change_month_url,
                    success: function(data){
                            $('.' + _self.properties.calendar_block_class).html(data.content);
                    }
            });
        }
        
        _self.Init(optionArr);

}
