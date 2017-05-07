/**
 * Questions js class
 *
 * @copyright Pilot Group <http://www.pilotgroup.net/>
 * @author Konstantin Rozhentsov
 * @version $Revision: 1
 */


function Questions(optionArr) {
    this.properties = {
        siteUrl: '',
        use_form: true,
        btnForm: 'btn-questions',
        btnRefresh: 'btn-refresh',
        cFormId: 'questions_form',
        urlGetForm: '',
        urlSendForm: '',
        urlGetData: '',
        compared: 0,
        error_sent: 'Question already sent',
        id_close: 'btn_send_questions',
        errorObj: new Errors,
        dataType: 'html',
        contentObj: new loadingContent({
            loadBlockWidth: '50%',
            closeBtnClass: 'w',
            scroll: true,
            closeBtnPadding: 5,
            blockBody: false,
        })
    };

    var _self = this;

    this.Init = function (options) {

        _self.properties = $.extend(_self.properties, options);

        $('#' + _self.properties.btnForm).bind('click', function () {
            if (_self.properties.compared == 1) {
                _self.properties.errorObj.show_error_block(_self.properties.error_sent, 'error');
                return false;
            }
            if (_self.properties.use_form) {
                _self.get_form();
            }
            return false;
        }).show();
    }

    this.get_form = function () {
        $.ajax({
            url: _self.properties.siteUrl + _self.properties.urlGetForm,
            type: 'POST',
            cache: false,
            dataType: _self.properties.dataType,
            beforeSend: function () {
                return preCheckAccess(_self.properties.urlGetForm);
            },
            success: function (data) {
                if (data.errors) {
                    error_object.show_error_block(data.errors, 'error');
                } else {
                    _self.properties.contentObj.show_load_block(data);
                    $('#' + _self.properties.id_close).unbind().bind('click', function () {
                        _self.clearBox();
                        return false;
                    });

                    $('#' + _self.properties.btnRefresh).unbind().bind('click', function () {
                        _self.refresh();
                    });

                }
            }
        });
        return false;
    }

    this.refresh = function () {
        $.ajax({
            url: _self.properties.siteUrl + _self.properties.urlGetData,
            type: 'POST',
            cache: false,
            dataType: 'json',
            success: function (data) {
                if (data['items']) {
                    $('#list').empty();
                    for (var i in data['items']) {
                        $('#list').append('<li><label><input type="radio" class="" value="' + data['items'][i]['id'] + '" id="question-' + data['items'][i]['id'] + '" name="question" /> ' + data['items'][i]['name'] + '</label></li>');
                    }
                }
            }
        });
    }

    this.send_form = function (data) {
        $.ajax({
            url: _self.properties.siteUrl + _self.properties.urlSendForm,
            type: 'POST',
            data: data,
            dataType: 'json',
            cache: false,
            success: function (data) {
                if (typeof (data.error) != 'undefined' && data.error != '') {
                    _self.properties.errorObj.show_error_block(data.error, 'error');
                } else {
                    _self.properties.compared = 1;
                    $('#' + _self.properties.btnForm).find('i').addClass('g gray');
                    $('#' + _self.properties.btnForm).css('color', '#808080');
                    _self.properties.errorObj.show_error_block(data.success, 'success');
                    _self.properties.contentObj.hide_load_block();
                }
            }
        });

        return false;
    }

    this.clearBox = function () {
        var data = $('#' + _self.properties.cFormId).serialize();

        _self.send_form(data);
    }

    _self.Init(optionArr);
}
