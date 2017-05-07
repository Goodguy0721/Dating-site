'use strict';
function AdminAccessPermissions(optionArr) {

    this.properties = {
        siteUrl: '/',
        dataAction: {
            add: '[data-action="add"]',
            addPeriod: '[data-action="add-period"]',
            edit: '[data-action="edit"]',
            delete: '[data-action="delete"]',
            save: '[data-action="save"]',
            status: '[data-action="status"]',
            changePermissions: '[data-action="change_permissions"]'
        },
        url: {
            saveSubscriptionType: 'admin/access_permissions/saveSubscriptionType/',
            loadSubscriptionForm: 'admin/access_permissions/loadSubscriptionForm/',
            editSubscription: 'admin/access_permissions/editSubscription/',
            deleteSubscription: 'admin/access_permissions/deleteSubscription/',
            statusSubscription: 'admin/access_permissions/statusSubscription/',
            loadPermissionsList: 'admin/access_permissions/loadPermissionsList/',
            loadPeriodForm: 'admin/access_permissions/loadPeriodForm/',
            reloadSubscriptionType: {
                all_users: 'admin/access_permissions/registered/',                	
                user_types: 'admin/access_permissions/userTypes/'
            }
        },
        id: {
            accessContent: '#access-content',
            addSubscriptionType: '#add-subscription-type',
            myTab: '#myTab',
            saveForm: '#save_form',
            permissionsList: '#permissions-list',
            periodsList: '#periods-list',
            sidebarMenu: '#sidebar-menu'
        },
        saveSubscriptionClass: '.subscription_type-js',
        scrollToBlock: 'scrollToBlock',
        errorObj: new Errors(),
        contentObj: new loadingContent({
            loadBlockWidth: '680px',
            closeBtnClass: 'load_content_controller_close',
            closeBtnPadding: 15,
            loadBlockSize: 'lg',
            footerButtons: '<input type="button" data-action="save" value="Save" class="btn btn-primary">'
        })
    };

    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.initControls();
        _self.scrollToBlock();
        _self.renderPage();
    };
    
    this.renderPage = function () {
        var mainWidth = $(window).width() - $(_self.properties.id.sidebarMenu).width();
        var permissionsListWidth = $(_self.properties.id.permissionsList).width();
        if (mainWidth < permissionsListWidth) {
            var rowWidth = (mainWidth-500)/$(_self.properties.id.permissionsList).find('th').length;
            $(_self.properties.id.permissionsList).find('th>div:not(:first)').width(rowWidth + 'px').addClass('text-ellipsis');  
            $(_self.properties.id.periodsList).find('th>div:not(:first)').width(rowWidth + 'px').addClass('text-ellipsis');     
        }
    };

    this.initControls = function () {
        $(document)
                .off('change', _self.properties.saveSubscriptionClass).on('change', _self.properties.saveSubscriptionClass, function () {
            _self.setSubscriptionType($(this));
        }).off('click', _self.properties.dataAction.add).on('click', _self.properties.dataAction.add, function () {
            _self.loadSubscriptionForm($(this));
        }).off('click', _self.properties.dataAction.edit).on('click', _self.properties.dataAction.edit, function () {
            _self.loadSubscriptionForm($(this));
        }).off('click', _self.properties.dataAction.delete).on('click', _self.properties.dataAction.delete, function () {
            _self.deleteSubscription($(this));
        }).off('click', _self.properties.dataAction.save).on('click', _self.properties.dataAction.save, function () {
            _self.saveSubscription();
        }).off('change', _self.properties.dataAction.status).on('change', _self.properties.dataAction.status, function () {
            _self.statusSubscription($(this));
        }).off('click', _self.properties.dataAction.changePermissions).on('click', _self.properties.dataAction.changePermissions, function () {
            _self.changePermissionsForm($(this));
        }).off('click', _self.properties.dataAction.addPeriod).on('click', _self.properties.dataAction.addPeriod, function () {
            _self.loadPeriodForm($(this));
        });
    };

    this.setSubscriptionType = function (obj) {
        $(_self.properties.saveSubscriptionClass).prop('checked', false).parent().removeClass('checked');
        var user_type = (typeof (obj.data('user_type')) !== 'undefined') ? obj.data('user_type') : 0;
        _self.query(
                _self.properties.url.saveSubscriptionType,
                {type: obj.data('type'), data: 1, user_type: user_type, send: 1},
                'json',
                function (data) {
                    obj.prop('checked', true).addClass('checked');
                    $(_self.properties.id.accessContent).html(data.html).find('input').iCheck({checkboxClass: 'icheckbox_flat-green'});
                    $(_self.properties.id.myTab).find('li:eq(0)').addClass('active');
                    locationHref(_self.properties.siteUrl + _self.properties.url.reloadSubscriptionType[obj.data('type')]);
                }
        );
    };

    this.loadSubscriptionForm = function (obj) {
        _self.query(
                _self.properties.url.loadSubscriptionForm,
                {id: obj.data('id'), send: 1},
                'json',
                function (data) {
                    _self.properties.contentObj.show_load_block(data.html);
                }
        );
    };

    this.deleteSubscription = function (obj) {
        if (typeof (obj.data('id')) !== 'undefined' && obj.data('id') > 0) {
            _self.query(
                _self.properties.url.deleteSubscription,
                {id: obj.data('id'), gid: obj.data('gid'), send: 1},
                'json',
                function (data) {
                    if (data.is_delete !== 0) {
                        obj.closest('tr').remove();
                        $('[data-group_actions="' +obj.data('gid') +  '"]').remove();
                    }
                }
            );
        }
    };

    this.saveSubscription = function () {
        var formObj = $(_self.properties.id.saveForm);
        _self.query(
                formObj.attr('action'),
                formObj.serialize(),
                'json',
                function (data) {
                    if (typeof (data.url_reload) !== 'undefined') {
                        lightSetCookie(
                            _self.properties.scrollToBlock, _self.getBodyScrollTop()
                        );
                        locationHref(data.url_reload);
                    } 
                 }
        );
    };

    this.statusSubscription = function (obj) {
        if (typeof (obj.data('id')) !== 'undefined' && obj.data('id') > 0) {
            _self.query(
                    _self.properties.url.statusSubscription,
                    {
                        id: obj.data('id'),
                        status: (obj.find('input').prop('checked') === true) ? 1 : 0,
                        send: 1
                    },
                    'json'
                    );
        }
    };

    this.changePermissionsForm = function (obj) {
        if (typeof (obj.data('module_gid')) !== 'undefined' && typeof (obj.data('access')) !== 'undefined') {
            _self.query(
                    _self.properties.url.loadPermissionsList,
                    {
                        module_gid: obj.data('module_gid'),
                        method: obj.data('method'),
                        access: obj.data('access'),
                        user_type: obj.data('user_type'),
                        send: 1
                    },
                    'json',
                    function (data) {
                        _self.properties.contentObj.show_load_block(data.html);
                    }
            );
        }
    };

    this.loadPeriodForm = function (obj) {
        _self.query(
                _self.properties.url.loadPeriodForm,
                {id: obj.data('id'), user_type: obj.data('user_type'), send: 1},
                'json',
                function (data) {
                    _self.properties.contentObj.show_load_block(data.html);
                }
        );
    };

    this.scrollToBlock = function () {
        var scroll = lightGetCookie(_self.properties.scrollToBlock);
        if (scroll > 0) {
            _self.clearBodyScrollTop();
            $('html,body').animate({scrollTop: scroll}, 100);
        }
    };

    this.getBodyScrollTop = function () {
        return _self.pageYOffset || (document.documentElement && document.documentElement.scrollTop) || (document.body && document.body.scrollTop);
    };

    this.clearBodyScrollTop = function () {
        lightSetCookie(_self.properties.scrollToBlock, 0);
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

}