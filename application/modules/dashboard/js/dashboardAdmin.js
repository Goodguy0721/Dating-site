'use strict';
function dashboardAdmin(optionArr) {

    this.properties = {
        siteUrl: '/',
        tempScrollTop: 0,
        id: {
            dashboard: '#dashboard',
            topNav: '#top_nav',
            scrollTop: '#scroll-top',
            eventBlock: '#event-block-'
        },
        cssClass: {
            dashboardAction: '.js-dashboard-action',
        },
        dataAction: {
            event: '[data-action="event"]',
            top: '[data-action="top"]'
        },
        errorObj: new Errors(),
        contentObj: null
    };

    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.initControls();
        _self.scrollBlock();
        _self.scrollToEventBlock();
    };

    this.initControls = function () {
        $(document).off('click', _self.properties.cssClass.dashboardAction).on('click', _self.properties.cssClass.dashboardAction, function (e) {
            _self.dashboardAction(e, $(this));
        }).off('click', _self.properties.id.scrollTop).on('click', _self.properties.id.scrollTop, function () {
            _self.scrollToTop();
        });
    };

    this.dashboardAction = function (e, obj) {
        e.preventDefault();
        e.stopPropagation();
        $.get(obj.prop('href'), {}, function () {
            var id = obj.closest(_self.properties.dataAction.event).data('id');
            if (typeof (Storage) !== "undefined") {
                sessionStorage.eventId = id;
                location.reload();
            } else {
                locationHref(_self.properties.siteUrl + 'admin/' + _self.properties.id.eventBlock + id);
            }
        });
    };

    this.scrollBlock = function () {
        var topMenuHeight = $(_self.properties.id.topNav).height();
        var dashboardContentObj = $(_self.properties.id.dashboard + ' .dashboard__content');
        dashboardContentObj.scroll(function () {
            if ($(this).scrollTop() > topMenuHeight) {
                $(_self.properties.id.scrollTop).fadeIn();
            } else {
                $(_self.properties.id.scrollTop).fadeOut();
            }
        });
        $(window).scroll(function () {
            if ($(this).scrollTop() > topMenuHeight) {
                dashboardContentObj.css('margin-top', '-' + topMenuHeight + 'px');
            } else {
                dashboardContentObj.css('margin-top', '-' + $(this).scrollTop() + 'px');
            }
        });
    };

    this.scrollToTop = function () {
        $(_self.properties.id.dashboard + ' .dashboard__content').animate({scrollTop: $(_self.properties.dataAction.top).offset().top}, 1000);
    };

    this.scrollToEventBlock = function () {
        if (typeof (Storage) !== "undefined") {
            if (sessionStorage.eventId) {
                var id = sessionStorage.eventId;
                var obj = (navigator.userAgent.match(/Firefox/)) ? $('body, html') : $( _self.properties.id.dashboard + ' .dashboard__content');
                obj.animate({scrollTop: $(_self.properties.id.eventBlock + id).offset().top - 50}, 500);
                delete sessionStorage.eventId;
            }
        }
    };

    _self.Init(optionArr);

}