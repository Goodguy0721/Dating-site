"use strict";
function usersConnections(optionArr) {

    this.properties = {
        siteUrl: '',
        addFacebookId: '#add_facebook',
        addVkId: '#add_vkontakte',
        addGoogleId: '#add_google',
        addTwitterId: '#add_twitter',
        template: '',
        contentObj: new loadingContent({
            loadBlockWidth: '400px',
            loadBlockLeftType: 'center',
            loadBlockTopType: 'top',
            loadBlockTopPoint: 100,
            closeBtnClass: 'w',
            draggable: true
        })
    };


    var _self = this;
    var _temp_obj = {};
    var _template = null;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.init_controls();
        if (_self.properties.template && typeof (twig) === 'function') {
            twig({
                id: "users_connections",
                href: _self.properties.template,
                async: false
            });
        }
    };

    this.uninit = function () {
        $(document)
            .off('click', '.' + _self.properties.addFacebookId)
            .off('click', '.' + _self.properties.addVkId)
            .off('click', '.' + _self.properties.addGoogleId)
            .off('click', '.' + _self.properties.addTwitterId);
        return this;
    };

    this.init_controls = function () {
        $(document)
          .off('click', _self.properties.addFacebookId).on('click', _self.properties.addFacebookId, function () {
            _self.addAccount($(this));
        }).off('click', _self.properties.addVkId).on('click', _self.properties.addVkId, function () {
            console.log('qqqqqqqqqqqqqqqqqqq')
            _self.addAccount($(this));
        }).off('click', _self.properties.addGoogleId).on('click', _self.properties.addGoogleId, function () {
            _self.addAccount($(this));
        }).off('click', _self.properties.addTwitterId).on('click', _self.properties.addTwitterId, function () {
            _self.addAccount($(this));
        });
    };

    this.addAccount = function (obj) {
        var service_user_id = $('input[name=service_user_id]').val();
        var user_type = $('select[name=user_type]').val();
        var url = obj.data('href') + '/' + user_type + '/' + service_user_id;
        $.ajax({
            type: 'POST',
            dataType: 'html',
            url: url,
            success: function (data) {
                _self.properties.contentObj.show_load_block(data);
            }
        });
    };

    _self.Init(optionArr);

}
