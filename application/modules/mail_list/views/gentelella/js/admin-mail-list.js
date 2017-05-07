function adminMailList(optionArr) {
    this.properties = {
        siteUrl: '',
        imgsUrl: '',
        subscribe_lang: '',
        unsubscribe_lang: ''
    }

    var _self = this;

    this.errors = {}

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
    }

    this.bind_users_events = function () {
        $('#mail_list').off('click', '.subscribe').on('click', '.subscribe', function () {
            _self.subscribe_users($(this).attr('id'));
            location.reload();
        });
        $('#mail_list').off('click', '.subscribe_one').on('click', '.subscribe_one', function () {
            var id_user = +$(this).parent().parent().attr('id').replace(/\D+/g, '');
            _self.subscribe_one(id_user, $(this));
        });
        $('#mail_list').off('click', '.unsubscribe_one').on('click', '.unsubscribe_one', function () {
            var id_user = +$(this).parent().parent().attr('id').replace(/\D+/g, '');
            _self.unsubscribe_one(id_user, $(this));
        });
    }

    this.bind_filters_events = function () {
        $('.link_delete', '#tbl_filters').unbind('click').bind('click', function () {
            var filter_id = +$(this).attr('id').replace(/\D+/g, '');
            _self.delete_filter(filter_id, $(this).parent().parent());
        })
        $('.link_search', '#tbl_filters').unbind('click').bind('click', function () {
            var filter_id = +$(this).attr('id').replace(/\D+/g, '');
            _self.apply_filter(filter_id);
        })
    }

    this.subscribe_one = function (id_user, btn) {
        _self.subscribe_users('subscribe_one', id_user);
        btn.removeClass('subscribe_one btn-outline').addClass('unsubscribe_one');
        btn.html(_self.properties.unsubscribe_lang);
    }

    this.unsubscribe_one = function (id_user, btn) {
        _self.subscribe_users('unsubscribe_one', id_user);
        btn.removeClass('unsubscribe_one').addClass('subscribe_one btn-outline');
        btn.html(_self.properties.subscribe_lang);
        btn.blur();
    }

    this.subscribe_users = function (action, id_user) {
        var data = {};
        var deltaCount = 0;
        data['id_subscription'] = $('#id_subscription').val();
        data['action'] = action;
        if ('subscribe_one' == action || 'unsubscribe_one' == action) {
            data['id_user'] = id_user;
        } else if ('subscribe_selected' == action || 'unsubscribe_selected' == action) {
            data['id_users'] = [];
            $('.grouping:checked').each(function () {
                id_user = $(this).attr('value');
                data['id_users'].push(id_user);
                if ('subscribe_selected' == action) {
                    $('#user' + id_user).find('.subscribe_one i')
                        .removeClass('fa-square-o').addClass('fa-check-square-o')
                        .parent().removeClass('subscribe_one').addClass('unsubscribe_one');
                } else {
                    $('#user' + id_user).find('.unsubscribe_one i')
                        removeClass('fa-check-square-o').addClass('fa-square-o')
                        .parent().removeClass('unsubscribe_one').addClass('subscribe_one');
                }
            });
        }
        $.ajax({
            url: _self.properties.siteUrl + 'admin/mail_list/ajax_subscribe',
            type: 'POST',
            cache: false,
            data: data
        });
        switch (action) {
            case 'subscribe_one' :
                deltaCount = 1;
                break;
            case 'unsubscribe_one' :
                deltaCount = -1;
                break;
            case 'subscribe_selected' :
                deltaCount = (data['id_users']).length;
                break;
            case 'unsubscribe_selected' :
                deltaCount = -(data['id_users']).length;
                break;
            case 'subscribe_all' :
                deltaCount = parseInt($('#count_not_subscribed').html());
                break;
            case 'unsubscribe_all' :
                deltaCount = -parseInt($('#count_subscribed').html());
                break;
        }
        _self.updateCount(deltaCount);
    }

    this.updateCount = function (count) {
        $('#count_subscribed').html(parseInt($('#count_subscribed').html()) + count);
        $('#count_not_subscribed').html(parseInt($('#count_not_subscribed').html()) - count);
    }

    this.save_filter = function (filter_data) {
        $.ajax({
            data: filter_data,
            type: 'POST',
            cache: false,
            url: _self.properties.siteUrl + 'admin/mail_list/ajax_save_filter'
        });
    }

    this.delete_filter = function (id_filter, row) {
        $.ajax({
            url: _self.properties.siteUrl + 'admin/mail_list/ajax_delete_filter',
            type: 'POST',
            cache: false,
            data: {id_filter: id_filter},
            success: function (response) {
                if (true == response) {
                    row.remove();
                    return true;
                } else {
                    console.error('Error while removing filter')
                    return false;
                }
            }
        });
    }

    this.apply_filter = function (id_filter) {
        $('#id_filter').val(id_filter);
        $('#frm_apply_filter').submit();
    }

    _self.Init(optionArr);
}
