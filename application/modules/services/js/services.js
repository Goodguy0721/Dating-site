"use strict";
function Services(optionArr) {

    this.properties = {
        selectMethodClass: 'select-payment-method',
        billingBlockClass: 'billing-systems-block',
        billingMethodClass: 'billing-method',
        systemGidId: 'system_gid'
    };
     

    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.initControls();
    };

    this.uninit = function () {
        $(document)
            .off('click', '.' + _self.properties.selectMethodClass)
            .off('click', '.' + _self.properties.billingMethodClass);
        return this;
    };

    this.initControls = function () {
        $(document)
          .off('click', '.' + _self.properties.selectMethodClass).on('click', '.' + _self.properties.selectMethodClass, function () {
                _self.toggleMethodBlock();
        }).off('click', '.' + _self.properties.billingMethodClass).on('click', '.' + _self.properties.billingMethodClass, function () {
                _self.setMethod($(this));
        });
    };
    
    this.toggleMethodBlock = function () {
        $('.' + _self.properties.billingBlockClass).toggle();
    };
    
    this.setMethod = function (obj) {
        $('.' + _self.properties.billingMethodClass).removeClass('selected');
        $(obj).addClass('selected');
        $('#' + _self.properties.systemGidId).val($(obj).data('gid'));
        
    };

    _self.Init(optionArr);

}
