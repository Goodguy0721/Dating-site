function Errors(optionArr) {
    this.properties = {
        errorBlockID: 'autogen_error_block',
        errorBlockMinWidth: '300px',
        errorBlockMaxWidth: '50%',
        errorAccess: 'ajax_login_link',
        showErrorTimeout: 7000,
        position: 'center', //// center, right
        dir: site_rtl_settings, /// rtl,
        showTO: null,
    }

    var _self = this;

    this.errors = {
    }

    this.typeErrors = {
        success: 'success',
        info: 'info',
        warning: 'warning',
        error: 'danger'
    };

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        PNotify.prototype.options.styling = "fontawesome";
        return _self;
    }

    this.extend_errors = function (errors) {
        _self.errors = $.extend(_self.errors, errors);
        return _self;
    }

    this.create_error_block = function () {

    }

    this.show_error_block = function (text, type) {
        if (type == 'error') {
            text = '<br><ul>' + text + '</ul>';
            new PNotify({
                title: text,
                type: type,
                max_width: '900px',
                width: '50%',
                hide: false,
                addclass: 'stack-center',
                stack: {"dir1": "down", "dir2": "right", "firstpos1": 25, "firstpos2": 0}
            });
        } else {
            new PNotify({
                title: text,
                type: type
            });
        }

        return _self;
    };

    this.hide_error_block = function () {
        return _self;
    };

    this.errors_access = function () {
        return false;
    };

    _self.Init(optionArr);

};
