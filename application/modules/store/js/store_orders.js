"use strict";
function storeOrders(optionArr) {

    this.properties = {
        siteUrl: '/',
        order: {
            id_order: 0,
            shipping_id: 0,
            price: 0,
            is_formed: 0,
            status: '',
        },
        data_order: [],
        form: '#order_block',
        id_country: '#country_name',
        id_region: '#region_name',
        id_city: '#city_name',
        id_hidden_country: '#id_hidden_country',
        id_hidden_region: '#id_hidden_region',
        id_hidden_city: '#id_hidden_city',
        forFriendBlock: '#for_friend',
        forMyselfBlock: '#for_myself',
        receiversBlock: '#receivers',
        friendListBlock: '#friendlist',
        addressBlock: '#address',
        sentListBlock: '#sent_gift',
        countryBlock: '#country_box',
        regionBlock: '#region_box',
        cityBlock: '#city_box',
        orderLogBlock: '#order_log_block_',
        addressId: '#last_address_',
        searchButton: '#search_friend',
        saveAddressButton: '#save_address',
        changeUser: '#change_user',
        friendsLoad: '#friends_load',
        userSearchForm: '#user_search',
        addressEdit: '.address_edit',
        countryButton: '#country_load',
        idAddressForm: '#shipping_address',
        streetAddress: '#street_address',
        shippingSelect: '#shipping_select',
        shippingShow: '#shipping_methods',
        shippingMethods: '#shipping-methods',
        shippingName: '#shipping_name',
        orderTotal: '#order_total',
        termsDelivery: '#terms_delivery',
        createOrder: '#create_order',
        phone: '#phone',
        zip: '#zip',
        comment: '#comment',
        regionLang: '#region_lang',
        cityLang: '#city_lang',
        addressRecipient: '#address_recipient',
        paymentBlock: '#payment-block',
        canceledSender: '.canceled_sender',
        canceledSenderSubmit: '#canceled_sender',
        servicesHelper: '#services_helper',
        shippingPrice: '.shipping_price',
        shippingTotal: '.shipping_total',
        orderCount: '.order_count',
        orderPrice: '.order_price',
        linkOrderLog: '.link-order_log',
        viewOrderHistory: '.view_order_history',
        deleteOrderHistory: '.delete_order_history',
        addUser: '.add-user',
        countrySelect: '.country-select',
        regionSelect: '.region-select',
        citySelect: '.city-select',
        lastAddress: '.last_address',
        removeAddress: '.address_remove',
        cancel: '.cancel',
        editShippingMethod: '.edit-shipping-method',
        changeUserLink: 'store/ajax_order_users/',
        searchUserLink: 'store/ajax_search_users/',
        selectUsersLink: 'store/ajax_select_users/',
        allFriendsLink: 'store/ajax_friends/',
        friendListLink: 'store/ajax_friend_list/',
        sentListsLink: 'store/ajax_sent_list/',
        addressFormLink: 'store/ajax_address_form/',
        userAddressLink: 'store/ajax_user_address/',
        countryLoadLink: 'store/ajax_load_countries/',
        regionCityLoadLink: 'store/ajax_load_region_city/',
        saveAddressLink: 'store/ajax_save_address/',
        removeAddressLink: 'store/ajax_delete_address/',
        termsDeliveryLink: 'store/ajax_terms_delivery/',
        saveOrderLink: 'store/save_order/',
        orderLogLink: 'store/ajax_show_order_log/',
        closePreorderLink: 'store/ajax_close_preorder/',
        deleteHistoryLink: 'store/ajax_delete_history/',
        confirmAddressLink: 'store/ajax_confirm_address/',
        shippingMethodsLink: 'store/ajax_load_shipping_methods/',
        timeout_obj: null,
        timeout: 500,
        commonAncestor: 'body',
        contentObj: new loadingContent({
            loadBlockWidth: '700px',
            loadBlockLeftType: 'center',
            loadBlockTopType: 'top',
            loadBlockTopPoint: 100,
            closeBtnClass: 'w',
            draggable: true
        })
    };

    var _self = this;
    var _loadFriends = true;
    var _loadOrderLog = true;
    var _loadCountries = true;
    var _friendsData = '';
    var _temp_obj = {};
    var _order_data = {
        for_friend: 0,
        for_myself: 0,
        user_id: 0,
        address_id: 0,
        shipping_id: 0,
        agree_terms_delivery: 0,
        comment: '',
    };

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.init_controls();
        if (_self.properties.order.is_formed == 1) {
            _self.loadPaymentData();
        }
        if (_self.properties.order.status == 'status_confirmed_recipient') {
            if ($(_self.properties.shippingMethods).length) {
                $('html, body').animate({scrollTop: $(_self.properties.shippingMethods).offset().top}, 1000);
            }
            var user_id = $(_self.properties.forFriendBlock + ' input').val();
            _self.shippingMethodsLoad(0, user_id);
        }
        if (_self.properties.data_order.for_friend || _self.properties.data_order.for_myself) {
            _self.loadOrderDaeta();
        }
    };

    this.uninit = function () {
        $(_self.properties.commonAncestor)
                .off('click', _self.properties.forFriendBlock)
                .off('click', _self.properties.forMyselfBlock)
                .off('click', _self.properties.changeUser)
                .off('click', _self.properties.addUser)
                .off('click', _self.properties.friendsLoad)
                .off('click', _self.properties.searchButton)
                .off('click', _self.properties.addressBlock)
                .off('click', _self.properties.addressEdit)
                .off('click', _self.properties.countryButton)
                .off('click', _self.properties.countryBlock)
                .off('click', _self.properties.regionBlock)
                .off('click', _self.properties.cityBlock)
                .off('keyup', _self.properties.id_city)
                .off('click', _self.properties.saveAddressButton)
                .off('keyup', _self.properties.phone)
                .off('click', _self.properties.lastAddress)
                .off('click', _self.properties.removeAddress)
                .off('click', _self.properties.shippingSelect)
                .off('click', _self.properties.shippingShow)
                .off('click', _self.properties.termsDelivery)
                .off('click', _self.properties.createOrder)
                .off('click', _self.properties.linkOrderLog)
                .off('click', _self.properties.canceledSender)
                .off('click', _self.properties.viewOrderHistory)
                .off('click', _self.properties.deleteOrderHistory)
                .off('click', _self.properties.addressRecipient)
                .off('click', _self.properties.canceledSenderSubmit)
                .off('click', _self.properties.cancel);
        return this;
    };

    this.init_controls = function () {
        $(_self.properties.commonAncestor).off('click', _self.properties.forFriendBlock).on('click', _self.properties.forFriendBlock, function () {
            _self.chooseFor(_self.properties.forFriendBlock);
        }).off('click', _self.properties.forMyselfBlock).on('click', _self.properties.forMyselfBlock, function () {
            _self.chooseFor(_self.properties.forMyselfBlock);
        }).off('click', _self.properties.changeUser).on('click', _self.properties.changeUser, function () {
            _self.loadUsers();
        }).off('click', _self.properties.addUser).on('click', _self.properties.addUser, function () {
            var receiver_id = $(this).data('userid');
            _self.selectUsers(receiver_id);
        }).off('click', _self.properties.friendsLoad).on('click', _self.properties.friendsLoad, function () {
            _self.allFriends();
        }).off('click', _self.properties.searchButton).on('click', _self.properties.searchButton, function () {
            _self.searchResult();
        }).off('click', _self.properties.addressEdit).on('click', _self.properties.addressEdit, function () {
            _self.addressForm($(this).data('addressid'));
        }).off('click', _self.properties.countryButton).on('click', _self.properties.countryButton, function () {
            _self.changeCountry();
        }).off('click', _self.properties.countryBlock).on('click', 'li.country', _self.properties.countryBlock, function () {
            _self.set_country($(this).attr('country'), $(this).text());
        }).off('click', _self.properties.regionBlock).on('click', 'li.region', _self.properties.regionBlock, function () {
            _self.set_region($(this).attr('region'), $(this).text());
        }).off('click', _self.properties.cityBlock).on('click', '.city', _self.properties.cityBlock, function () {
            _self.set_city($(this).attr('city'), $(this).text());
        }).off('keyup', _self.properties.id_region).on('keyup', _self.properties.id_region, function () {
            _self.changeRegion($(this).val());
        }).off('keyup', _self.properties.id_city).on('keyup', _self.properties.id_city, function () {
            _self.changeCity($(this).val());
        }).off('click', _self.properties.saveAddressButton).on('click', _self.properties.saveAddressButton, function () {
            _self.saveAddress($(this).data('addressid'));
        }).off('keyup', _self.properties.phone).on('keyup', _self.properties.phone, function () {
            _self.formatPhone(this);
        }).off('click', _self.properties.lastAddress).on('click', _self.properties.lastAddress, function () {
            _self.addressChoose($(this).data('addressid'));
        }).off('click', _self.properties.removeAddress).on('click', _self.properties.removeAddress, function () {
            _self.addressDelete($(this));
        }).off('click', _self.properties.shippingSelect).on('click', _self.properties.shippingSelect, function () {
            _self.shippingMethods();
        }).off('click', _self.properties.shippingShow).on('click', 'div.shipping_methods', _self.properties.shippingShow, function () {
            _self.changeShipping($(this));
        }).off('click', _self.properties.termsDelivery).on('click', _self.properties.termsDelivery, function (event) {
            _self.loadTermsDelivery($(this), event);
        }).off('click', _self.properties.createOrder).on('click', _self.properties.createOrder, function () {
            _self.saveOrder();
        }).off('click', _self.properties.linkOrderLog).on('click', _self.properties.linkOrderLog, function () {
            _self.loadOrderLog();
        }).off('click', _self.properties.canceledSender).on('click', _self.properties.canceledSender, function () {
            _self.cancelOrder($(this));
        }).off('click', _self.properties.viewOrderHistory).on('click', _self.properties.viewOrderHistory, function () {
            _self.viewHistory($(this));
        }).off('click', _self.properties.deleteOrderHistory).on('click', _self.properties.deleteOrderHistory, function () {
            _self.deleteHistory($(this));
        }).off('click', _self.properties.addressRecipient).on('click', _self.properties.addressRecipient, function () {
            _self.saveAddressRecipient();
        }).off('click', _self.properties.canceledSenderSubmit).on('click', _self.properties.canceledSenderSubmit, function () {
            _self.canceledOrder();
        }).off('click', _self.properties.cancel).on('click', _self.properties.cancel, function () {
            _self.properties.contentObj.hide_load_block();
        }).off('submit', _self.properties.userSearchForm).on('submit', _self.properties.userSearchForm, function () {
            _self.searchResult();
            return false;

        });
    };

    this.chooseFor = function (id) {
        $(id + ' .block').removeClass('select-for');
        $(id + ' i').removeClass('check');
        switch (id) {
            case _self.properties.forFriendBlock:
                $(_self.properties.forFriendBlock + ' input').prop('disabled', false);
                $(_self.properties.forMyselfBlock + ' .block').addClass('select-for');
                $(_self.properties.forMyselfBlock + ' i').addClass('check');
                var user_id = $(_self.properties.forFriendBlock + ' input').val();
                _order_data.for_friend = 1;
                _order_data.user_id = user_id;
                _order_data.for_myself = 0;
                _self.addressLoad(false);
                break;
            case _self.properties.forMyselfBlock:
                $(_self.properties.forFriendBlock + ' input').prop('disabled', true);
                $(_self.properties.forFriendBlock + ' .block').addClass('select-for');
                $(_self.properties.forFriendBlock + ' i').addClass('check');
                _order_data.user_id = _order_data.for_friend = 0;
                _order_data.for_myself = 1;
                _self.addressLoad(true);
                break;
        }
    };

    this.addressChoose = function (id, callback) {
        $(_self.properties.lastAddress + ' .block').addClass('select-for');
        $(_self.properties.lastAddress + ' i').addClass('check');
        $(_self.properties.lastAddress + ' input').prop('disabled', true);
        $(_self.properties.addressId + id + ' .block').removeClass('select-for');
        $(_self.properties.addressId + id + ' i').removeClass('check');
        $(_self.properties.addressId + id + ' input').prop('disabled', false);
        _order_data.address_id = id;
        $(_self.properties.shippingTotal).html(0);
        var orders = $(_self.properties.orderPrice).data('price');
        _self.totalPrise(0, orders);
        _self.shippingMethodsLoad(id, 0, callback);
    };

    this.shippingMethodsLoad = function (id, user_id, callback) {
        if (id == 'undefined')
            id = 0;
        var data = {id: id, user_id: user_id}
        _request(_self.properties.shippingMethodsLink, '', data, 'json', function (data) {
            var result = data;
            $(_self.properties.shippingMethods).html(result.html);
            if (callback) {
                callback();
            }
        });
    };

    this.addressLoad = function (load) {
        if (load) {
            $(_self.properties.addressBlock).show("drop", {direction: "right"}, 500);
        } else {
            $(_self.properties.addressBlock).hide("drop", {direction: "right"}, 500);
        }
    };

    this.loadUsers = function () {
        _request(_self.properties.changeUserLink, '', '', 'html', function (data) {
            _self.showLoadBlock(data);
            _self.friendsList();
            _self.mySentGifts();
        });
    };

    this.friendsList = function () {
        _request(_self.properties.friendListLink, '', '', 'html', function (data) {
            $(_self.properties.friendListBlock).html(data);
        });
    };

    this.mySentGifts = function () {
        _request(_self.properties.sentListsLink, '', '', 'html', function (data) {
            $(_self.properties.sentListBlock).html(data);
        });
    };

    this.selectUsers = function (receiver_id) {
        _order_data.user_id = receiver_id;
        _request(_self.properties.selectUsersLink, receiver_id, '', 'html', function (data) {
            _self.properties.contentObj.hide_load_block();
            $(_self.properties.receiversBlock).html(data);
            _self.chooseFor(_self.properties.forFriendBlock);
            _loadFriends = true;
        });
    };

    this.allFriends = function () {
        if (_loadFriends) {
            _request(_self.properties.allFriendsLink, '', '', 'html', function (data) {
                _loadFriends = false;
                _friendsData = data;
                $(_self.properties.friendListBlock).html(data);
                $(_self.properties.friendsLoad).hide();
            });
        } else {
            $(_self.properties.friendListBlock).html(_friendsData);
        }
        $(_self.properties.friendsLoad).hide();
    };

    this.searchResult = function () {
        var search_data = $(_self.properties.userSearchForm).serialize();
        _request(_self.properties.searchUserLink, '', search_data, 'html', function (data) {
            $(_self.properties.friendListBlock).html(data);
        });
    };

    this.addressForm = function (id) {
        _request(_self.properties.addressFormLink, id, '', 'html', function (data) {
            _self.showLoadBlock(data);
            if (_order_data.comment) {
                $(_self.properties.comment).val(_order_data.comment);
            }
        });
    };

    this.saveAddress = function (id) {
        $('.error').html('');
        var data = {
            'country': $(_self.properties.id_hidden_country).val(),
            'region': $(_self.properties.id_hidden_region).val(),
            'city': $(_self.properties.id_hidden_city).val(),
            'street_address': $(_self.properties.streetAddress).val(),
            'phone': $(_self.properties.phone).val(),
            'zip': $(_self.properties.zip).val(),
        };
        _order_data.comment = $(_self.properties.comment).val();
        _request(_self.properties.saveAddressLink, _self.properties.order.id_order + "/" + id, data, 'json', function (data) {
            var result = data;
            if (result['address_id']) {
                _self.properties.contentObj.hide_load_block();
                $(_self.properties.addressBlock).html(result['html']);
                _self.addressChoose(result['address_id']);
                $(_self.properties.addressRecipient).show();
            } else if (result.errors) {
                alert(esult.errors)
                for (var key in result.errors) {
                    $('#error-' + key).show().html(result.errors[key]);
                }
            }
        });
    };

    this.changeCountry = function () {
        if (_loadCountries) {
            _loadCountries = false;
            $(_self.properties.countryBlock + ' ul').empty();
            _request(_self.properties.countryLoadLink, '', '', 'json', function (data) {
                var result = data['countries'];
                if (result) {
                    for (var key in result) {
                        $(_self.properties.countryBlock + ' ul').append('<li class="country" gid="rs_' + result[key].id + '" country="' + result[key].code + '">' + result[key].name + '</li>');
                    }
                    //var indent = $('label').width();
                    //$(_self.properties.countryBlock).css({"margin-left": indent+5,"width": "300"}).slideDown();
                    //var widthInput = $(_self.properties.id_country).outerWidth();
                    $(_self.properties.countryBlock).slideDown();
                }
            });
        } else {
            $(_self.properties.countryBlock).slideUp();
            _loadCountries = true;
        }
    };

    this.set_country = function (country, value) {
        $(_self.properties.id_country).val(value);
        $(_self.properties.id_country).html(value);
        $(_self.properties.id_hidden_country).val(country);
        _self.destroyLocations();
        _self.closeBox(_self.properties.countryBlock);
    };

    this.set_region = function (region, value) {
        $(_self.properties.id_region).val(value);
        $(_self.properties.id_hidden_region).val(region);
        _self.closeBox(_self.properties.regionBlock);
    };

    this.set_city = function (city, value) {
        $(_self.properties.id_city).val(value);
        $(_self.properties.id_hidden_city).val(city)
        _self.closeBox(_self.properties.cityBlock);
    };

    this.destroyLocations = function () {
        _self.set_region('', '');
        _self.set_city('', '');
    };

    this.changeRegion = function (value) {
        if (_self.properties.timeout_obj) {
            clearTimeout(_self.properties.timeout_obj);
        }
        _self.properties.timeout_obj = setTimeout(function () {
            $(_self.properties.regionBlock + ' ul').empty();
            var country = $(_self.properties.id_hidden_country).val();
            if (!country) {
                _self.changeCountry();
                return;
            }
            ;
            var data = {'country': country, 'data': value};
            _request(_self.properties.regionCityLoadLink, '', data, 'json', function (data) {
                var result = data;
                for (var key in result['regions']) {
                    $(_self.properties.regionBlock + ' ul').append('<li class="region" gid="rs_' + key + '" region="' + result['regions'][key].id + '">' + result['regions'][key].name + '</li>');
                }
                //var widthInput = $(_self.properties.id_region).width();
                $(_self.properties.regionBlock).slideDown();
            });
        }, _self.properties.timeout);
        return true;
    };

    this.changeCity = function (value) {
        if (_self.properties.timeout_obj) {
            clearTimeout(_self.properties.timeout_obj);
        }
        _self.properties.timeout_obj = setTimeout(function () {
            $(_self.properties.cityBlock + ' ul').empty();
            var country = $(_self.properties.id_hidden_country).val();
            var region = $(_self.properties.id_hidden_region).val();
            var data = {'country': country, 'region': region, 'data': value};
            _request(_self.properties.regionCityLoadLink, '', data, 'json', function (data) {
                var result = data;
                for (var key in result['cities']) {
                    $(_self.properties.cityBlock + ' ul').append('<li class="city" gid="rs_' + key + '" city="' + result['cities'][key].id + '">' + result['cities'][key].name + '</li>');
                }
                //var widthInput = $(_self.properties.id_city).width();
                $(_self.properties.cityBlock).slideDown();
            });
        }, _self.properties.timeout);
        return true;
    };

    this.addressDelete = function (obj) {
        var id = obj.closest('.last_address').data('addressid');
        _request(_self.properties.removeAddressLink, id, '', 'html', function (data) {
            $(_self.properties.addressId + data).hide("drop", {direction: "right"}, 500, function () {
                $(this).remove();
            });
            var count_address = $(_self.properties.lastAddress).length;
            if (count_address < 2) {
                $(_self.properties.addressRecipient).hide();
            }
        });
    };

    this.formatPhone = function (obj) {
        if (obj.value.match(/[^0-9]/g)) {
            obj.value = obj.value.replace(/[^0-9]/g, '');
        }
    };

    this.closeBox = function (id) {
        $(id).slideUp();
        $(id + ' ul').empty();
    };

    this.shippingMethods = function () {
        var data = [];
        data['title'] = $(_self.properties.shippingMethods + ' h1').html();
        data['content'] = $(_self.properties.shippingShow).html();
        _self.showLoadBlock(data, true);
        $('.load_content').css('background', '#FFFFFF');
        $('.load_content').find(_self.properties.editShippingMethod).hide();
    };

    this.changeShipping = function (obj) {
        if (obj.data('shippingid')) {
            $('.shipping_methods').removeClass('select-shipping');
            obj.addClass('select-shipping');
            return;
        }
        $(_self.properties.shippingName).html($('.select-shipping').html());
        $(_self.properties.editShippingMethod).show();
        var price = $('.select-shipping').find(_self.properties.shippingPrice);
        $(_self.properties.shippingTotal).html(price.html());
        var shipping = $('.select-shipping').data('price');
        var shipping_id = $('.select-shipping').data('shippingid');
        var orders = $(_self.properties.orderPrice).data('price');
        _self.totalPrise(shipping, orders);
        _order_data.shipping_id = shipping_id;
        _self.properties.contentObj.hide_load_block();
    };

    this.totalPrise = function (shipping, orders) {
        var total = (parseFloat(shipping) + parseFloat(orders)).toFixed(2);
        $(_self.properties.orderTotal).html(currency_output(total));
    };

    this.loadTermsDelivery = function (obj, e) {
        var status = obj.find('input').prop('checked');
        _order_data.agree_terms_delivery = status ? 1 : 0;
        if (e.target.tagName == 'SPAN') {
            _request(_self.properties.termsDeliveryLink, '', '', 'json', function (data) {
                var result = data;
                _self.showLoadBlock(result, true);
            });
        }
    };

    this.showLoadBlock = function (data, wrap) {
        if (wrap) {
            data = "<div class='content-block load_content'><h1>" + data['title'] + "</h1><div class='m10'>" + data['content'] + "</div></div>";
        }
        _self.properties.contentObj.show_load_block(data);
    };

    this.saveOrder = function () {
        if (!_order_data.shipping_id)
            _order_data.shipping_id = _self.properties.order.shipping_id;
        var data = _order_data;
        var hidden = '';
        for (var key in data) {
            hidden += "<input type='hidden' name='" + key + "' value='" + data[key] + "'>";
        }
        $(_self.properties.form).prepend(hidden).submit();
    };

    this.canceledOrder = function () {
        var hidden = "<input type='hidden' name='canceled_sender' value='1'>";
        $(_self.properties.form).prepend(hidden).submit();
    };

    this.loadOrderLog = function () {
        if (_loadOrderLog) {
            _request(_self.properties.orderLogLink, _self.properties.order.id_order, '', 'json', function (data) {
                var result = data;
                $(_self.properties.orderLogBlock + _self.properties.order.id_order).html(result['html']);
                _loadOrderLog = false;
            });
        } else {
            $(_self.properties.orderLogBlock + _self.properties.order.id_order).toggle();
        }
    };

    this.cancelOrder = function (obj) {
        var order_id = obj.data('orderid');
        var data = {'canceled_sender': 1};
        _request(_self.properties.closePreorderLink, order_id, data, 'json', function (data) {
            var result = data;
            if (result.errors) {
                error_object.show_error_block(data.errors, 'error');
            } else if (result) {
                obj.closest('div.js-order-product').hide("drop", {direction: "right"}, 500, function () {
                    var prev_div = $(this).prev();
                    $(this).remove();
                    if ($(_self.properties.canceledSender).length == 0) {
                        prev_div.prev().hide();
                        prev_div.show();
                    }
                });
            }
        });
    };

    this.viewHistory = function (obj) {
        var order_id = obj.data('id');
        if (obj.hasClass('fa-folder-o')) {
            _request(_self.properties.orderLogLink, order_id, '', 'json', function (data) {
                var result = data;
                obj.removeClass('fa-folder-o').addClass('fa-folder-open-o');
                $(_self.properties.orderLogBlock + order_id).html(result['html']).show("slide", {direction: "up"}, 500);
            });
        } else {
            obj.removeClass('fa-folder-open-o').addClass('fa-folder-o');
            $(_self.properties.orderLogBlock + order_id).hide("slide", {direction: "up"}, 500, function () {
                $(this).html('');
            });
        }
    };

    this.deleteHistory = function (obj) {
        var order_id = obj.data('id');
        _request(_self.properties.deleteHistoryLink, order_id, '', 'html', function (data) {
            obj.closest('div.js-order-product').hide("drop", {direction: "right"}, 500, function () {
                var prev_div = $(this).prev();
                $(this).remove();
                if ($(_self.properties.deleteOrderHistory).length == 0) {
                    prev_div.prev().hide();
                    prev_div.show();
                }
            });
            $(_self.properties.orderLogBlock + order_id).hide("drop", {direction: "right"}, 500, function () {
                $(this).remove();
            });
        });
    };

    this.saveAddressRecipient = function () {
        var address = {'address_id': _order_data.address_id, 'comment': _order_data.comment, 'order_id': _self.properties.order.id_order};
        _request(_self.properties.confirmAddressLink, '', address, 'json', function (data) {
            var result = data;
            if (result['success']) {
                error_object.show_error_block(result['success'], 'success');
            } else if (result['errors']) {
                error_object.show_error_block(result['errors'], 'error');
            }
        });
    };

    this.loadPaymentData = function () {
        if ($(_self.properties.servicesHelper).length) {
            $('#service_id_order_payment').val(function (i, val) {
                if (!val) {
                    return _self.properties.order.id_order;
                }
                return val;
            });
            $('#service_id_shipping').val(function (i, val) {
                if (!val) {
                    return _self.properties.order.shipping_id;
                }
                return val;
            });
            $('#service_price').val(function (i, val) {
                if (!val) {
                    return _self.properties.order.price;
                }
                return val;
            });
            $('input[name=price]').val(function (i, val) {
                if (!val) {
                    return _self.properties.order.price;
                }
                return val;
            });
        }
    };

    this.loadOrderDaeta = function () {
        if (_self.properties.data_order.for_myself != 0) {
            _self.chooseFor(_self.properties.forMyselfBlock);
            if (_self.properties.data_order.address_id) {
                _self.addressChoose(_self.properties.data_order.address_id, function () {
                    if (_self.properties.data_order.shipping_id) {
                        $('#shipping_method_' + _self.properties.data_order.shipping_id).addClass('select-shipping');
                        $('div.shipping_methods>input').click();
                        return;
                    }
                });
                return;
            }
            return;
        } else if (_self.properties.data_order.for_friend != 0) {
            _self.chooseFor(_self.properties.forFriendBlock);
        }
    };

    var _request = function (url, param, data, dataType, successCb) {
        $.ajax({
            url: _self.properties.siteUrl + url + param,
            type: 'POST',
            cache: false,
            data: data,
            dataType: dataType,
            success: function (data) {
                if (data.errors) {
                    error_object.show_error_block(data.errors, 'error');
                } else {
                    successCb(data);
                }
            }
        });
    };


    _self.Init(optionArr);
}
