function mainMenu(optionArr) {
    'use strict'
    
    this.properties = {
        siteUrl: '',
        slidemenu: 'slidemenu',
        slidemenuId: 'slidemenu-outer',
        closeBtnId: 'slidemenu-close',
        buttonDropdownMenu: 'button.dropdown-toggle',
        dropdownMenu: '.dropdown-menu',
        dataSlidemenu: '[data-slidemenu="#slidemenu"]'
    }
    
    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        
        if (lightGetCookie('statusMainMenu') === 'open') {
            $('#' + _self.properties.slidemenu).show();
            $('#' + _self.properties.slidemenuId).show();
        }
        
        $(_self.properties.dataSlidemenu).off('click').on('click', function() {
            $('#' + _self.properties.slidemenu).show();
            $('#' + _self.properties.slidemenuId).show();
            _self.delMenuCookie();
            lightSetCookie('statusMainMenu', 'open');
        });
        
        $('#' + _self.properties.closeBtnId).off('click').on('click', function() {
            $('#' + _self.properties.slidemenuId).hide();
            _self.delMenuCookie();
            lightSetCookie('statusMainMenu', 'closed');
        });
        $(document).off('click', _self.properties.buttonDropdownMenu).on('click', _self.properties.buttonDropdownMenu, function () {
            _self.dropdownPosition($(this));
        });
    };
    
    this.uninit = function () {
        $(document).off('click', _self.properties.buttonDropdownMenu);
        return this;
    };
    
    this.dropdownPosition = function (obj) {
        var indent = parseInt($(window).height() - ($(obj).offset().top - $(window).scrollTop()));
        var heightEl = parseInt($(obj).siblings(_self.properties.dropdownMenu).height());
        if (indent < heightEl) {
            var marginTop = heightEl + 50;
            $(obj).siblings(_self.properties.dropdownMenu).css('margin-top', '-' + marginTop + 'px');
        } else {
            $(obj).siblings(_self.properties.dropdownMenu).css('margin-top', '2px');
        }
    };
    
     this.delMenuCookie = function () {
        var expiresDate = new Date();
        expiresDate.setTime(expiresDate.getTime() - 1);
        document.cookie = "statusMainMenu=;expires=" + expiresDate.toGMTString();
    }
    
    _self.Init(optionArr);

    return _self;
}
