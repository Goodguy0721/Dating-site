'use strict';
function fastNavigationAdmin(optionArr) {

    this.properties = {
        siteUrl: '/',
        id: {fastBlock: 'fastBlock'},
        dataAction: {
            search: '[data-action="fast_search"]',
            searchLong: '[data-action="fast_search_long"]'
        },
        url: {
            search: 'admin/fast_navigation/search/'
        },
        langs: {
            header: 'Search',
            placeholder: 'Start typing...',
            close: 'Close'
        },
        errorObj: new Errors(),
        contentObj: null
    };

    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.initControls();
        _self.properties.contentObj = new loadingContent({
            closeBtnPadding: 15,
            loadBlockSize: 'lg',
            loadBlockTopType: 'top',
            closeBtnLabel: _self.properties.langs.close
        })
    };

    this.initControls = function () {
        $(document).off('keyup', _self.properties.dataAction.search).on('keyup', _self.properties.dataAction.search, function () {
            _self.search($(this));
        }).off('keyup', _self.properties.dataAction.searchLong).on('keyup', _self.properties.dataAction.searchLong, function () {
            _self.searchResults($(this));
        });
    };
    
    this.searchResults = function (obj) {
        var keyword = obj.val();
        if (keyword.length > 2) {
            _self.query(
                _self.properties.url.search,
                {keyword: keyword, send: 1},
                'json',
                function (data) {   
                    $('#' + _self.properties.id.fastBlock).html(data.html);
                }
            );
        } else {
            _self.properties.contentObj.hide_load_block();
            $(_self.properties.dataAction.search).focus();
        }
    };

    this.search = function (obj) {
        var keyword = obj.val();
        if (keyword.length > 2) {
            _self.properties.contentObj.show_load_block(_self.wrapBlock());
            $(_self.properties.dataAction.searchLong).focus().val(keyword);
        }
    };
    
    this.wrapBlock = function() {
        var htmlObj = '<div>';
              htmlObj += '  <h3>' +_self.properties.langs.header  + '</h3>';
              htmlObj += '  <div class="load_content"><div class="mb20">';
              htmlObj += '      <input data-action="fast_search_long" type="text" name="keyword" class="form-control" placeholder="' + _self.properties.langs.placeholder + '" required>';
              htmlObj += '  </div>';
              htmlObj += '  <div class="x_panel" id="' + _self.properties.id.fastBlock + '"></div></div>';  
              htmlObj += '</div>';
              return htmlObj;        
    };

    this.query = function (url, data, dataType, cb) {
        if (!/^(f|ht)tps?:\/\//i.test(url)) {
            url = _self.properties.siteUrl + url;
        }
        $.ajax({
            url: url,
            type: 'POST',
            cache: false,
            data: data,
            dataType: dataType,
            success: function (data) {
                if (typeof (data.error) !== 'undefined' && data.error.length > 0) {
                    _self.properties.errorObj.show_error_block(data.errors, 'error');
                }
                if (typeof (data.success) !== 'undefined' && data.success.length > 0) {
                    _self.properties.errorObj.show_error_block(data.success, 'success');
                }
                if (typeof (cb) !== 'undefined') {
                    cb(data);
                }
            }
        });
        return false;
    };

    _self.Init(optionArr);

}