//"use strict";
function ajaxDeclineMoneyTransaction(optionArr) {
    
    this.properties = {
        siteUrl: '',
        reloadTimeout: 600,
        idTransaction: 0,
        rand: 0,
        urlCheckTransaction: 'send_money/ajaxDecline/',
        errorObj: new Errors
    };

    var _self = this;
        
    this.Init = function(options) {
        _self.properties = $.extend(_self.properties, options);
    };
    
    this.declineMoneyTransaction = function (idTransaction, rand) {
                $.ajax({
                        type: 'POST',
                        url: _self.properties.siteUrl + _self.properties.urlCheckTransaction + idTransaction,
                        success: function(data){
                                if (data) {
                                    _self.properties.errorObj.show_error_block(data, 'success');
                                    $('#status_' + rand).html('<font class="donate decline">'+data+'</font>');
                                } else {
                                    console.log('return error');
                                }
                        }
                });
    };
    
    _self.Init(optionArr);
}

function ajaxApproveMoneyTransaction(optionArr) {
    
    this.properties = {
        siteUrl: '',
        reloadTimeout: 600,
        idTransaction: 0,
        rand: 0,
        urlCheckTransaction: 'send_money/ajaxApprove/',
        errorObj: new Errors
    };

    var _self = this;
        
    this.Init = function(options) {
        _self.properties = $.extend(_self.properties, options);
    };
    
    this.approveMoneyTransaction = function (idTransaction, rand) {
        $.ajax({
            type: 'POST',
            url: _self.properties.siteUrl + _self.properties.urlCheckTransaction + idTransaction,
            data: {'id_transaction': idTransaction},
            success: function(data) {
                if (data) {
                    _self.properties.errorObj.show_error_block(data, 'success');
                    $('#status_' + rand).html('<font class="donate approve">'+data+'</font>');
                } else {
                    console.log('return error');
                }
            }
        });
    };
    
    _self.Init(optionArr);
}

function ajaxValidateMoneyTransaction(optionArr) {
  
    this.properties = {
        siteUrl: '',
        reloadTimeout: 600,
        urlCheckTransaction: 'send_money/ajaxValidateTransaction/',
        errorObj: new Errors
    };

    var _self = this;
        
    this.Init = function(options) {
        _self.properties = $.extend(_self.properties, options);
        _self.validateTransaction();
    };
    
    this.validateTransaction = function () {
        $('#send_money').unbind('click').bind('click', function(e){
            e.preventDefault();
            $.ajax({
                type: 'POST',
                url: _self.properties.siteUrl + _self.properties.urlCheckTransaction,
                data: $('#send_form').serialize(),
                dataType: 'json',
                success: function(data) {
                    if(typeof(data.errors) != 'undefined' && data.errors != ''){
                        _self.properties.errorObj.show_error_block(data.errors, 'error');
                    } else {
                        $('#send_form').submit();
                    }
                }
            });
        });
    };
    
    _self.Init(optionArr);
}
