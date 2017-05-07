'use strict';
function AccessPermissions(optionArr) {
    this.properties = {
        siteUrl: '/',
        id: {
            accessPermissions: '#access_permissions',
            periodsProgressBar: '#periods-progress-bar',
            setPrice: '#set-price',
            paySystemsList: '#pay-systems-list',
            paymentForm: '#payment-form',
            saving: '#saving'
        },
        class: {
            periodBlock: '.period-block',
            allPeriod: '.all-period',
            progressBarSelected: '.progress-bar-selected',
            progressBarEmpty: '.progress-bar-empty',
            periodDescription: '.period-description',
            progressBar: '.progress-bar',
            saving: '.saving',
            setData: '.set-data-js',
            breadcrumbs: '.breadcrumbs',
            advertisingImage: '.advertising-image',
            itemGroup: '.item-group',
            accessList: '.access-list',
            selectedJs: '.selected-js',
            singlPeriod: '.singl-period'
        },
        dataAction: {
            selectGroup: '[data-action="select-group"]',
            setPrice: '[data-action="set-price"]',
            selectedPeriod: '[data-action="selected-period"]',
            paySystems: '[data-action="pay-systems"]',
            setPaySystem: '[data-action="set-paysystem"]',
            payment: '[data-action="payment"]',
            close:'[data-action=close]',
            groupToggle:'[data-action=groupToggle]',
            saving:'[data-action=saving]'
        },
        dataContent: {
           advertisingImage: '[data-content=advertising-image]' 
        },
        url: {
            loadGroupPage: 'access_permissions/group/',
            groupPage: 'access_permissions/groupPage/',
            selectedPeriod: 'access_permissions/selectedPeriod/',
            paymentForm: 'access_permissions/paymentForm/',
            payment: 'access_permissions/payment/'
        },
        lang: {
            systemError: 'System error!'
        },
        headerAdvertisingImage: false,
        currency: '',
        calculation: false,
        group_gid:null,
        errorObj: new Errors(),
        contentObj: new loadingContent({
            loadBlockWidth: '400px',
            loadBlockLeftType: 'center',
            loadBlockTopType: 'top',
            loadBlockTopPoint: 100,
            draggable: true,
            closeBtnUse: true,
            closeBtnClass: 'btn-close'
        })
    };

    var _self = this;
    var _tempData = {group_gid:_self.properties.group_gid};
    var _offsetData = [
        'col-xs-12',
        'col-md-4 col-md-offset-4',
        'col-md-8 col-md-offset-2',
        'col-md-10 col-md-offset-1'
    ];

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.initControls();
        _self.rendering();
    };

    this.initControls = function () {
        $(document)
           .off('click', _self.properties.class.periodBlock).on('click', _self.properties.class.periodBlock, function () {
            _self.togglePeriods($(this));
        }).off('click', _self.properties.class.allPeriod).on('click', _self.properties.class.allPeriod, function () {
            _self.setPeriod($(this));
        }).off('click', _self.properties.dataAction.selectGroup).on('click', _self.properties.dataAction.selectGroup, function () {
            _self.selectGroup($(this));
        }).off('click', _self.properties.id.periodsProgressBar + '>' + _self.properties.class.progressBar).on('click', _self.properties.id.periodsProgressBar + '>' + _self.properties.class.progressBar, function () {
            _self.changeSelectedPeriod($(this));
        }).off('click', _self.properties.dataAction.setPrice).on('click', _self.properties.dataAction.setPrice, function () {
            _self.changeSelectedPeriod($(this));
        }).off('click', _self.properties.dataAction.selectedPeriod).on('click', _self.properties.dataAction.selectedPeriod, function () {
            _self.selectedPeriod($(this));
        }).off('click', _self.properties.dataAction.setPaySystem).on('click', _self.properties.dataAction.setPaySystem, function () {
            _self.loadPaymentForm($(this));
        }).off('click', _self.properties.dataAction.payment).on('click', _self.properties.dataAction.payment, function () {
            _self.payment($(this));
        }).off('click', _self.properties.dataAction.close).on('click', _self.properties.dataAction.close, function () {
            _self.close();
        }).off('click', _self.properties.dataAction.groupToggle).on('click', _self.properties.dataAction.groupToggle, function () {
            _self.groupToggle($(this));
        }).off('click', _self.properties.class.setData).on('click', _self.properties.class.setData, function () {
            _self.setGroupData($(this));
        }).off('click', _self.properties.dataAction.paySystems).on('click', _self.properties.dataAction.paySystems, function () {
            if ($(_self.properties.class.singlPeriod).length > 0) {
                _self.setData($(_self.properties.class.singlPeriod));
            }
        });
    };

    this.rendering = function () {
        var count = $(_self.properties.class.itemGroup).length;
        if (count < 5) {
            $(_self.properties.class.accessList).parent().addClass(_offsetData[count]);
        } else {
            $(_self.properties.class.accessList).parent().addClass(_offsetData[0]);
        }
        _self.headerAdvertisingImage();
        if (_self.properties.calculation) {
            _self.savingCalculation();
        }
    };
    
    this.headerAdvertisingImage = function () {
        if (_self.properties.headerAdvertisingImage !== false) {
            if ($(_self.properties.class.advertisingImage).is(':visible') === false) {
                $(_self.properties.class.breadcrumbs).hide();
                $('header').after(function () {
                    _self.properties.headerAdvertisingImage = false;
                    return $(_self.properties.dataContent.advertisingImage).html();
                });
            }
        } else {
            $(_self.properties.class.advertisingImage).remove();
            $(_self.properties.class.breadcrumbs).show();
        }
    };

    this.togglePeriods = function (obj) {
        $('#period-' + obj.data("group") + '-all').toggle();
    };

    this.setPeriod = function (obj) {
        $('#period-' + obj.data("group") + '-all').hide();
        $('#period-' + obj.data("group")).find('div').html(function(){
            return obj.data("price") + "<span class='currency'>" + _self.properties.currency + "</span>";
        });
        $('#period-' + obj.data("group")).find('span.period').html(obj.find('span').html());
        $('#period-' + obj.data("group")).attr({
            'data-group': obj.data("group"),
            'data-id': obj.data("id"),
            'data-period': obj.data("period"),
            'data-price': obj.data("price")
        });
        $(_self.properties.dataAction.selectGroup).attr(
            'href',
            _self.properties.siteUrl + _self.properties.url.groupPage + obj.data("group") + '/' + obj.data("id")
        );
    };

    this.selectGroup = function (obj) {
        var period = $('#period-' + obj.data('group'));
        _self.query(
                _self.properties.url.loadGroupPage,
                {period_id: period.data('id'), group_gid: period.data('group'), send: 1},
                'json',
                function (data) {
                    _self.properties.headerAdvertisingImage = false;
                    _self.headerAdvertisingImage();
                    _self.setData(period);
                    $(_self.properties.id.accessPermissions).html(data.html);
                    _self.setPrice();
                    _self.savingCalculation();
                }
        );
    };
    
    this.setData = function (obj) {
        _tempData.period = obj.data('period');
        _tempData.period_id = obj.data('id');       
        _tempData.price = obj.data('price'); 
        if (typeof (obj.data('group')) !== 'undefined') {
            _tempData.group_gid = obj.data('group'); 
        } 
        if (typeof (obj.data('key')) !== 'undefined') {
            _tempData.key = obj.data('key'); 
        } 
    };
    
    this.setPrice = function (price) {
         _tempData.price = (typeof (price) !== 'undefined')  ? price : _tempData.price;
        $(_self.properties.id.setPrice).html(_tempData.price);
    };
    
    this.changeSelectedPeriod = function (obj) {  
        if (typeof _tempData.period_id === 'undefined') {
            _self.setData(
                $(_self.properties.class.selectedJs)
             );
        }
        $(_self.properties.class.progressBar).each(function(){
            if ($(this).data('key') <= obj.data('key')) {     
                $(this).addClass('progress-bar-selected').removeClass('progress-bar-empty');
            } else {
                 $(this).addClass('progress-bar-empty').removeClass('progress-bar-selected');
            }
        }); 
        $(_self.properties.id.periodsProgressBar).find('i').remove();
        $('[data-id="' + _tempData.period_id + '"]').find(_self.properties.class.periodDescription).prepend('<div class="delimiter">|</div>');
        $('[data-id="' + obj.data('id') + '"]').find(_self.properties.class.periodDescription).find('.delimiter').remove();  
        $('[data-id="' + obj.data('id') + '"]').find(_self.properties.class.periodDescription).prepend('<i class="fa fa-circle"></i>');
        _self.setData(obj);
        _self.setPrice();
        _self.savingCalculation();
    };
    
    this.loadPaymentForm = function (obj) {
        var paymentSystem = obj.data('gid');
        if (_tempData.group_gid === null) {
            _tempData.group_gid = _self.properties.group_gid;
        }
        _self.query(
                _self.properties.url.paymentForm,
                {period_id: _tempData.period_id, group_gid: _tempData.group_gid, pay_system_gid: paymentSystem, send: 1},
                'json',
                function (data) { 
                    _self.properties.contentObj.show_load_block(data.html);
                }
        );
    };
    
    this.payment = function (obj) {
        if (obj.data('pay_system') === 'account') {
            _self.query(
                _self.properties.url.payment,
                {period_id: obj.data('period'), group_gid: obj.data('group'), pay_system_gid: obj.data('pay_system'), send: 1},
                'json',
                function (data) { 
                    _self.properties.contentObj.show_load_block(data.html);
                }
            );
        } else {
            $(_self.properties.id.paymentForm).submit();
        }
    };
    
    this.close = function () {
        _self.properties.contentObj.hide_load_block();
    };
    
    this.groupToggle = function (obj) {
        if ($('#' + obj.data('group') + '-block .module').length > 0) {
            var groupObj = $('#' + obj.data('group') + '-block');
            if (groupObj.hasClass('active') === false) {
                groupObj.addClass('active');
            } else {
                groupObj.removeClass('active');
            }
        }
    };
    
    this.savingCalculation = function () {
        if (typeof _tempData.period_id === 'undefined') {
            _self.setData(
                $(_self.properties.class.selectedJs)
             );
        }
        var obj = $(_self.properties.id.periodsProgressBar + '>div').first();
        var price = obj.data('price')/obj.data('period');
        var saving = _tempData.period*price-_tempData.price;
        if (saving > 0) {
            $(_self.properties.id.saving).show();
            $(_self.properties.dataAction.saving).html(saving);
        } else {
            $(_self.properties.id.saving).hide();
        }
    };
    
    this.setGroupData = function (obj) {
        var group = obj.data('group');
        var data = $('#period-' + group);
        _self.setData(data);
        if ($('#' + group + '-block .module').length === 0) {
           var groupObj = $('#' + group + '-block');
           if (groupObj.hasClass('active') === false && 
                   $('#pay-systems-list-' + group).is(':visible') === false) {
                groupObj.addClass('active');
            } else {
                groupObj.removeClass('active');
            }
        }
    };

    this.query = function (url, data, dataType, cb) {
        if (!/^(f|ht)tps?:\/\//i.test(url)) {
            url = _self.properties.siteUrl + url;
        }
        $.ajax({
            url: url,
            type: 'POST',
            cache: false,
            data: data,
            dataType: dataType,
            success: function (data) {
                if (typeof (data.error) !== 'undefined' && data.error.length > 0) {
                    _self.properties.errorObj.show_error_block(data.error, 'error');
                }
                if (typeof (data.info) !== 'undefined' && data.info.length > 0) {
                    _self.properties.errorObj.show_error_block(data.info, 'info');
                }
                if (typeof (data.success) !== 'undefined' && data.success.length > 0) {
                    _self.properties.errorObj.show_error_block(data.success, 'success');
                }
                if (typeof (cb) !== 'undefined') {
                    cb(data);
                }
            }
        });
        return false;
    };

    _self.Init(optionArr);
};