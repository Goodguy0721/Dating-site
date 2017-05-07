function Errors(optionArr) {
    this.properties = {
        errorBlockID: 'autogen_error_block',
        errorStaticBlockID: 'static-alert-block',
        errorBlockMinWidth: '300px',
        errorBlockMaxWidth: '50%',
        errorAccess: 'ajax_login_link',
        showErrorTimeout: 7000,
        position: 'center', //// center, right
        dir: site_rtl_settings, /// rtl,
        showTO: null,
        expires: 604800,
        path: '/',
        domain: '',
        secure: false,
    };

    var _self = this;

    this.errors = {
    };

    this.typeErrors = {
        success: 'alert-success',
        info: 'alert-info',
        warning: 'alert-warning',
        error: 'alert-danger'
    };

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.create_error_block();

        if (typeof _self.properties.expires == "number" && _self.properties.expires) {
            var d = new Date();
            d.setTime(d.getTime() + _self.properties.expires * 1000);
            _self.properties.expires = _self.properties.expires = d;
        }

        if (_self.properties.expires && _self.properties.expires.toUTCString) {
            _self.properties.expires = _self.properties.expires.toUTCString();
        }

        return _self;
    };

    this.uninit = function () {
        $(document).off('click', 'button[data-action=close]');
        return this;
    };

    this.extend_errors = function (errors) {
        _self.errors = $.extend(_self.errors, errors);
        return _self;
    };

    this.create_error_block = function () {
        if (!$("#" + _self.properties.errorBlockID).attr("id")) {
            $("body").append('<div id="' + _self.properties.errorBlockID + '"></div>');
            $("#" + _self.properties.errorBlockID).css({
                'display': 'none',
                'overflow': 'hidden',
                'position': 'fixed',
                'z-index': '2001',
                'min-width': _self.properties.errorBlockMinWidth,
                'max-width': _self.properties.errorBlockMaxWidth
            });
            $("#" + _self.properties.errorBlockID).attr('title', _self.errors.dblclick);
            $("#" + _self.properties.errorBlockID).bind('click', function (event) {
                _self.hide_error_block();
            });
            $(document).off('click', 'button[data-action=close]').on('click', 'button[data-action=close]', function () {
                _self.hideStaticErrorsblock($(this));
            });
        }
        return _self;
    };

    this.show_error_block = function (text, type) {
        $("#" + _self.properties.errorBlockID).hide();

        if (typeof text === 'object') {
            if (text.length) {
                text = text.join('<br>');
            } else {
                var messages = text;
                text = [];
                for (var key in messages) {
                    text.push(messages[key]);
                }
                text = text.join('<br>');
            }
        }

        if (text === _self.properties.errorAccess) {
            _self.errors_access();
        } else if (typeof text !== 'undefined') {
            $("#" + _self.properties.errorBlockID).html('<div class="ajax_notice"><div class="' + type + ' ' + _self.typeErrors[type] + ' alert-warning_pop_">' + text + '</div></div>');
        }

        if (_self.properties.dir == 'ltr') {
            var posPropertyLeft = "left";
            var posPropertyRight = "right";
        } else {
            var posPropertyLeft = "right";
            var posPropertyRight = "left";
        }

        if (_self.properties.position == 'left') {
            $("#" + _self.properties.errorBlockID).css('top', '10px');
            $("#" + _self.properties.errorBlockID).css(posPropertyLeft, '10px');
        } else if (_self.properties.position == 'center') {
            $("#" + _self.properties.errorBlockID).css('top', '50px');
            var left = ($(window).width() - $("#" + _self.properties.errorBlockID).width()) / 2;
            $("#" + _self.properties.errorBlockID).css(posPropertyLeft, left + 'px');
        } else if (_self.properties.position == 'right') {
            $("#" + _self.properties.errorBlockID).css('top', '10px');
            $("#" + _self.properties.errorBlockID).css(posPropertyRight, '10px');
        }

        if (text !== _self.properties.errorAccess)
            $("#" + _self.properties.errorBlockID).fadeIn('slow');

        if (_self.properties.showTO) {
            clearTimeout(_self.properties.showTO);
        }
        _self.properties.showTO = setTimeout(function () {
            _self.hide_error_block();
        }, _self.properties.showErrorTimeout)

        return _self;
    };

    this.showStaticErrorsblock = function (text, type) {

        if (typeof text === 'object') {
            if (text.length) {
                text = text.join('<br>');
            } else {
                var messages = text;
                text = [];
                for (var key in messages) {
                    text.push(messages[key]);
                }
                text = text.join('<br>');
            }
        }

        $('#' + _self.properties.errorStaticBlockID).html(function (indx, oldHtml) {
            if (oldHtml == '') {
                return '<div class="' + type + ' ' + _self.typeErrors[type] + '">' + text + '</div>';
            }
        }).fadeIn('slow');

        return _self;
    };

    this.hide_error_block = function () {
        $("#" + _self.properties.errorBlockID).fadeOut('slow');
        return _self;
    };

    this.errors_access = function () {
        $('html, body').animate({scrollTop: $("#" + _self.properties.errorAccess).offset().top}, 2000);
        $("#" + _self.properties.errorAccess).click();
        return false;
    };

    this.hideStaticErrorsblock = function (obj) {

        var cookie = $(obj).data('cookie') + "=" + encodeURIComponent(1);

        if (_self.properties.expires)
            cookie += ';expires=' + _self.properties.expires;
        if (_self.properties.path)
            cookie += ';path=' + _self.properties.path;
        if (_self.properties.domain)
            cookie += ';domain=' + _self.properties.domain;
        if (_self.properties.secure)
            cookie += ';secure=true'

        document.cookie = cookie;

        $('#' + _self.properties.errorStaticBlockID).fadeOut('slow');
    };

    _self.Init(optionArr);

}
;
