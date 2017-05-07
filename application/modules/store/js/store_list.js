"use strict";
function storeList(optionArr) {
    this.properties = {
        siteUrl: '/',
        categoriesId: 'categories',
        sortId: 'sort_product',
        categoriesBlockId: 'cat-load-block',
        searchProductId: 'search_product',
        catBlockClass: 'cat-block',
        categoriesListClass: 'categories-list',
        categoriesLink: 'store/ajax_load_categories/',
        categoryLink: '',
        sortLink: 'store/',
        commonAncestor: 'body',
    };

    var _self = this;
    var _load_cat = true;
    var _cat_height = 0;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        _self.init_controls();
    };

    this.uninit = function () {
        $(document)
                .off('click', '#' + _self.properties.categoriesId)
                .off('change', '#' + _self.properties.sortId)
                .off('click', '#' + _self.properties.searchProductId);
        return this;
    };

    this.init_controls = function () {
        $(document).off('click', _self.properties.commonAncestor).on('click', _self.properties.commonAncestor, function (event) {
            _self.categoriesClose(event);
        }).off('click', '#' + _self.properties.categoriesId).on('click', '#' + _self.properties.categoriesId, function () {
            _self.categoriesLoad();
        }).off('change', '#' + _self.properties.sortId).on('change', '#' + _self.properties.sortId, function () {
            _self.sortProducts($(this));
        }).off('click', '#' + _self.properties.searchProductId).on('click', '#' + _self.properties.searchProductId, function () {
            _self.searchProduct();
        });
    };

    this.categoriesClose = function (event) {
        if (!$(event.target).closest('#' + _self.properties.categoriesId).length) {
            if (!$(event.target).closest('#' + _self.properties.categoriesBlockId).length) {
                $('#' + _self.properties.categoriesBlockId).hide();
                event.stopPropagation();
            }
        }
    };

    this.categoriesLoad = function () {
        if (_load_cat) {
            _request(_self.properties.categoriesLink, '', '', 'json', function (data) {
                var result = data;
                $('#' + _self.properties.categoriesBlockId).html(result['html']).show(function () {
                    _cat_height = $('.' + _self.properties.categoriesListClass).height();
                });
                _load_cat = false;
                if (_cat_height > 250) {
                    $('.cat-scroller').slimScroll({railVisible: true, size: '5px', position: 'right'});
                    $('.' + _self.properties.categoriesListClass).hover(
                            function () {
                                $('.cat-scroller').slimScroll({railVisible: true, size: '5px', position: 'right'});
                                $('.slimScrollBar').show();
                                $('.slimScrollRail').show();
                            },
                            function () {
                                $('.slimScrollBar').hide();
                                $('.slimScrollRail').hide()
                            }
                    );
                }
            });
        } else {
            $('#' + _self.properties.categoriesBlockId).toggle();
            if (_cat_height > 250) {
                $('.cat-scroller').slimScroll({railVisible: true, size: '5px', position: 'right'});
            }
        }

    };

    this.sortProducts = function (obj) {
        var gid = obj.val();
        if (gid !== 'undefined') {
            document.location.href = _self.properties.siteUrl + _self.properties.category_id + '/' + gid;
        }
    };

    this.searchProduct = function () {
        $('.' + _self.properties.catBlockClass).hide("drop", {direction: "left"}, 500);
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
