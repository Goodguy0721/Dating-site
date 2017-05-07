//"use strict";
function Associations(optionArr) {

    this.properties = {
        siteUrl: '',
        profile_id: null,
        profile: {},
        lang: {},
        limits: 10,
        compared: '0',
        rand: '',
        btn_id: '',
        link_id: '',
        association_content_id: 'association_content',
        name_id: '',
        image_id: '',
        btn_compare_id: '',
        btn_more_id: '',
        last_association_id: '',
        next_association_id: '',
        association_action_id: 'association_action_',
        association_id: 'association_',
        association_action_class: 'association-action',
        load_associations_url: 'associations/ajaxLoadAssociations',
        view_associations_url: 'associations/ajaxViewAssociations',
        set_compare_url: 'associations/ajaxSetCompare',
        set_answer_url: 'associations/ajaxSetAnswer',
        common_ancestor: 'body',
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
        _self.initIds();
        _self.init_controls();

        if (_self.properties.template && typeof (twig) === 'function') {
            twig({
                id: "association",
                href: _self.properties.template,
                async: false
            });

            _template = twig({ref: "association"});
        }
    };

    this.initIds = function () {
        _self.properties.btn_id = 'btn-association-' + _self.properties.rand;
        _self.properties.link_id = 'link-association-' + _self.properties.rand;
        _self.properties.btn_compare_id = 'btn_compare_' + _self.properties.rand;
        _self.properties.btn_more_id = 'btn_more_' + _self.properties.rand;
        _self.properties.image_id = 'association_image_' + _self.properties.rand;
        _self.properties.last_association_id = 'last_association_' + _self.properties.rand;
        _self.properties.name_id = 'association_name_' + _self.properties.rand;
        _self.properties.next_association_id = 'next_association_' + _self.properties.rand;
    };

    this.uninit = function () {
        $(_self.properties.common_ancestor)
                .off('click', '#' + _self.properties.btn_id)
                .off('click', '#' + _self.properties.btn_more_id)
                .off('click', '#' + _self.properties.btn_compare_id)
                .off('click', '#' + _self.properties.last_association_id)
                .off('click', '#' + _self.properties.next_association_id)
                .off('click', '.' + _self.properties.association_action_class);
        return this;
    };

    this.init_controls = function () {
        $(_self.properties.common_ancestor)
                .off('click', '#' + _self.properties.btn_id).on('click', '#' + _self.properties.btn_id, function () {
            _self.loadAssociations();
        }).off('click', '#' + _self.properties.btn_more_id).on('click', '#' + _self.properties.btn_more_id, function () {
            _self.getAssociation();
        }).off('click', '#' + _self.properties.btn_compare_id).on('click', '#' + _self.properties.btn_compare_id, function () {
            _self.setCompare($(this));
        }).off('click', '#' + _self.properties.last_association_id).on('click', '#' + _self.properties.last_association_id, function () {
            _self.getLastAssociation();
        }).off('click', '#' + _self.properties.next_association_id).on('click', '#' + _self.properties.next_association_id, function () {
            _self.getNextAssociation();
        }).off('click', '.' + _self.properties.association_action_class + '>a').on('click', '.' + _self.properties.association_action_class + '>a', function () {
            _self.answeAssociation($(this).data('id'), $(this).data('answer'));
        });
    };

    this.loadAssociations = function () {
        if (_temp_obj.compared === '1' || _self.properties.compared === '1') {
            error_object.show_error_block(_self.properties.lang.already_sent, 'error');
        } else {
            $.ajax({
                type: 'POST',
                dataType: 'html',
                url: _self.properties.siteUrl + _self.properties.load_associations_url,
                beforeSend: function () {
                    return preCheckAccess(_self.properties.load_associations_url);
                },
                success: function (data) {
                    _self.getAssociation(data);
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (typeof (console) !== 'undefined') {
                        console.error(errorThrown);
                    }
                }
            });
        }
    };

    this.getAssociation = function (content) {
        if (typeof (_temp_obj.count_associations) === 'undefined') {
            var post_data = {profile_id: _self.properties.profile_id, limits: _self.properties.limits};
            $.ajax({
                type: 'POST',
                dataType: 'JSON',
                url: _self.properties.siteUrl + _self.properties.view_associations_url,
                data: post_data,
                success: function (data) {
                    if (typeof (data.errors) != 'undefined' && data.errors != '') {
                        error_object.show_error_block(data.errors, 'error');
                    } else {
                        if (data.images.length !== 0) {
                            if (typeof (content) !== 'undefined') {
                                _self.properties.contentObj.show_load_block(content);
                            }
                            _temp_obj.profile_id = data.profile_id;
                            _temp_obj.count_associations = data.images.length;
                            _temp_obj.association_obj = [];
                            for (var key in data.images) {
                                _temp_obj.association_obj.push(data.images[key]);
                            }
                            _temp_obj.association_last_key = _temp_obj.count_associations - 1;
                            _self.viewAssociation(0);
                        } else {
                            _self.properties.contentObj.hide_load_block();
                            $('#' + _self.properties.btn_id).find('i').addClass('g');
                            $('#' + _self.properties.link_id).remove();
                            error_object.show_error_block(_self.properties.lang.associations_empty, 'error');
                        }
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    if (typeof (console) !== 'undefined') {
                        console.error(errorThrown);
                    }
                }
            });
        } else if (_temp_obj.association_key >= _temp_obj.count_associations) {
            _temp_obj.association_last_key = _temp_obj.count_associations - 1;
            _self.appendAssociations();
        } else {
            if (!$('#' + _self.properties.association_content_id).is(':visible')) {
                _self.properties.contentObj.show_load_block(content);
            }
            _self.viewAssociation(_temp_obj.association_key);
        }
    };

    this.getLastAssociation = function () {
        if (_temp_obj.association_key <= 1) {
            _self.viewAssociation(_temp_obj.association_last_key);
        } else {
            var last_key = parseInt(_temp_obj.association_key) - 2;
            _self.viewAssociation(last_key);
        }
    };

    this.getNextAssociation = function () {
        _self.getAssociation();
    };

    this.viewAssociation = function (key) {
        _temp_obj.association_key = parseInt(key) + 1;
        var check_image = _self.checkImage(key);
        if (check_image === true) {
            var html_data = '';
            var chevronLeftHeightPosition = '';
            var chevronRightHeightPosition = '';

            $('#' + _self.properties.association_content_id).html(function (index, old_html) {

                if (_template) {
                    return _template.render({'properties': _self.properties, obj: _temp_obj, key: key});
                } else {
                    html_data += '<div class="mtb10 ib addHeight">';
                    html_data += '<div id="' + _self.properties.last_association_id + '" class="icon-chevron-left hover fleft pointer"></div>';
                    html_data += '<div id="' + _self.properties.image_id + '" class="mlr20 fleft wdth180 helper"><img class="imgChild" src="' + _temp_obj.association_obj[key].image.thumbs.big + '" ></div>';
                    html_data += '<div id="' + _self.properties.next_association_id + '" class="icon-chevron-right hover fleft pointer"></div>';
                    html_data += '</div>';
                    html_data += '<div class="clr"></div>';
                    html_data += '<div class="mtb10" id="' + _self.properties.name_id + '">' + _temp_obj.association_obj[key].name + '</div>';
                    html_data += '<div class="mtb10"><input data-profile="' + _temp_obj.profile_id + '" data-id="' + _temp_obj.association_obj[key].id + '" type="button" id="' + _self.properties.btn_compare_id + '" value="' + _self.properties.lang.compare + '" name="compare" >&nbsp;<input type="button" id="' + _self.properties.btn_more_id + '" value="' + _self.properties.lang.more + '" name="more" ></div>';
                    return html_data;
                }
            });

            var chevronMargin = $("#association_content").children().first().height() / 2;

            $('#' + _self.properties.last_association_id).css('margin-top', chevronMargin + 'px');
            $('#' + _self.properties.next_association_id).css('margin-top', chevronMargin + 'px');
        } else {
            _self.getNextAssociation();
        }
    };

    this.appendAssociations = function () {
        var post_data = {profile_id: _self.properties.profile_id, limits: _self.properties.limits, last_id: _temp_obj.association_obj[_temp_obj.association_last_key].id};
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: _self.properties.siteUrl + _self.properties.view_associations_url,
            data: post_data,
            success: function (data) {
                if (data.images.length) {
                    _temp_obj.count_associations = _temp_obj.count_associations + data.images.length;
                    for (var key in data.images) {
                        _temp_obj.association_obj.push(data.images[key]);
                    }
                    _self.viewAssociation(_temp_obj.association_last_key + 1);
                } else {
                    _self.viewAssociation(0);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.error(errorThrown);
                }
            }
        });
    };

    this.checkImage = function (key) {
        var req = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
        if (!req) {
            throw new Error('XMLHttpRequest not supported');
        }
        req.open('HEAD', _temp_obj.association_obj[key].image.thumbs.big, false);
        req.send(null);
        if (req.status === 200) {
            return true;
        } else {
            _temp_obj.association_obj.splice(key, 1);
            _temp_obj.count_associations = (_temp_obj.count_associations > 0) ? (_temp_obj.count_associations - 1) : 0;
            return false;
        }
        return false;
    };

    this.setCompare = function (obj) {
        var post_data = {association_id: $(obj).data('id'), profile_id: $(obj).data('profile')};
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: _self.properties.siteUrl + _self.properties.set_compare_url,
            data: post_data,
            success: function (data) {
                if (!data.errors) {
                    _temp_obj.compared = '1';
                    $('#' + _self.properties.btn_id).find('i').addClass('g');
                    $('#' + _self.properties.link_id).remove();
                    error_object.show_error_block(data.success, 'success');
                } else {
                    error_object.show_error_block(data.error, 'error');
                }
                _self.properties.contentObj.hide_load_block();
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.error(errorThrown);
                }
            }
        });
    };

    this.answeAssociation = function (association_id, answer_gid) {
        var post_data = {association_id: association_id, answer: answer_gid};
        $.ajax({
            type: 'POST',
            dataType: 'JSON',
            url: _self.properties.siteUrl + _self.properties.set_answer_url,
            data: post_data,
            success: function (data) {
                if (data.success) {
                    var answer = _self.createAnswer(answer_gid);
                    $('#' + _self.properties.association_action_id + association_id).html('');
                    $('#' + _self.properties.association_id + association_id).append(answer);
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.error(errorThrown);
                }
            }
        });
    };

    this.createAnswer = function (answer_gid) {
        var html_data = '';
        html_data += '<div class="fright answer"><div class="content fleft"><i class="icon-caret-right icon-4x fltr"></i><div class="association-block">' + _self.properties.lang.answer[answer_gid] + '</div></div><div class="image small fright"><img src="' + _self.properties.profile.media.user_logo.thumbs.small + '" alt="' + _self.properties.profile.nickname + '" title="' + _self.properties.profile.nickname + '" /><div>' + _self.properties.profile.nickname + ', ' + _self.properties.profile.age + '</div></div></div>';
        return html_data;
    };

    this.escapeRegExp = function (str) {
        return str.replace(/[]/g, "\\$&");
    };

    this.addJSParams = function (data) {
        var str = 'siteUrl: site_url,';
        var reg = new RegExp(_self.escapeRegExp(str).replace(/[]/g, '|'), 'gi');
        return data.replace(reg, '$&' + ' singleton: 0,');
    };

    _self.Init(optionArr);

}
