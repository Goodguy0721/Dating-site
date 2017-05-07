"use strict";
function mobileTopMenu(optionArr) {

    this.properties = {
        siteUrl: '',
        mMenu: '.mobile-top-menu',
        mobileMenu: '.mobile-menu-wrapper',
        mobileMenuItem: '.mobile-menu-item',
        scrollToTop: '.scroll-to-top',
        siteheader: 'header',
        mainMenu: '#main-menu-container',
        tempScrollTop: 0,
        fixedMenuScroll: 10,
    };
     

    var _self = this;
    var _objEl = [
        'h1',
        '.title-block',
        '.footer-menu-title-block'
    ];

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.init_controls();
        _self.buildMenu();
        $(window).scroll(function(){
            $('.mobile-menu-wrapper').slick('slickGoTo', 0, true);
            var top = $(document).scrollTop();
            if (top < _self.properties.fixedMenuScroll) {
                $(_self.properties.mMenu).css({top: '0', position: 'relative'});
                $(_self.properties.mainMenu).css({'position': 'relative'});
            } else { 
                $(_self.properties.mMenu).css({top: '46px', position: 'fixed'});
                $(_self.properties.mainMenu).css({'position': 'fixed'});
            }
        });
        _self.scrollToTop();
    };

    this.uninit = function () {
         $(document)
            .off('click', _self.properties.mobileMenuItem)
            .off('focus keypress keyup change blur', 'textarea, input[type=text]');
        return this;
    };

    this.init_controls = function () {
        $(document)
          .off('click', _self.properties.mobileMenuItem).on('click', _self.properties.mobileMenuItem, function () {
            _self.scrollToBlock($(this));
        }).off('focus keypress keyup change blur', 'textarea, input[type=text]').on('focus keypress keyup change blur', 'textarea, input[type=text]', function () {
            _self.textBoxFocus();
        });
    };
    
    this.buildMenu = function () {
        $('div').find(_objEl.join()).each(function(){
            var menuTitle = $(this).data('title');
            var idBlock = $(this).data('id');
            if (typeof menuTitle !== 'undefined' && typeof idBlock !== 'undefined') {
                _self.createMenu(menuTitle, idBlock);
            }
        });
        $(_self.properties.mobileMenu).slick({
            accessibility: false,
            dots: false,
            infinite: true,
            speed: 0,
            slidesToShow: 3,
            slidesToScroll: 1,
            prevArrow: false,
            nextArrow: false
        });
    };

    this.createMenu = function (menuItem, idBlock) {
        $(_self.properties.mobileMenu).append('<div class="mobile-menu-item" data-id="' + idBlock + '">' + menuItem + '<s data-mblock-id="' + idBlock + '"></s></div>');
    };
    
    this.scrollToBlock = function (obj) {   
        $(_self.properties.mobileMenuItem).removeClass('active');
        $(obj).addClass('active');
        var idBlock = $(obj).data('id');
        var slideIndex = 0;
        if (idBlock != 'pjaxcontainer') {
            slideIndex = parseInt($(obj).index()) - 4;
        } else {
            slideIndex = parseInt($(obj).index()) - 3;
        }
        $('.mobile-menu-wrapper').slick('slickGoTo', slideIndex, true);
        $('html, body').animate({scrollTop: ($('#' + idBlock).offset().top - $('.main-inner-content').offset().top)}, 800);
    }
    
    this.scrollToTop = function () {
        $(window).scroll(function () {
            if ($(this).scrollTop() > 150 && $(this).width() < 768) {
                if (_self.properties.tempScrollTop > $(this).scrollTop()) {
                    $(_self.properties.scrollToTop).fadeIn("fast");
                    $(_self.properties.mMenu).fadeIn("fast");
                    $(_self.properties.siteheader).fadeIn("fast");
                    
                    if ($('#cookie_policy_block').is(':visible')) {
                        $(_self.properties.scrollToTop).css('bottom', '120px');
                    }

                } else {
                    $(_self.properties.scrollToTop).fadeOut("fast");
                    $(_self.properties.mMenu).fadeOut("fast");
                    $(_self.properties.siteheader).fadeOut("fast");
                }


             } else {
                $(_self.properties.scrollToTop).fadeOut();
             }
            _self.properties.tempScrollTop = $(this).scrollTop();
        });
    };
    
    this.textBoxFocus = function () {
        $(_self.properties.scrollToTop).fadeOut();
    };

    _self.Init(optionArr);

}
