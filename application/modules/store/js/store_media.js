function store_media(optionArr) {
    this.properties = {
        gallery_name: 'storegallery',
        siteUrl: '/',
        idProduct: 0,
        mediaId: '',
        count_photo: 0,
        photoSize: 'smalle',
        deleteMediaUrl: 'admin/store/product_media_delete_',
        deleteMediaUrlReplace: 'admin/store/ajax_product_media_delete_',
        viewMediaUrl: 'admin/store/ajax_view_product_media',
        galleryContentDiv: 'store_photos',
        galleryContentUrl: 'admin/store/ajax_get_product_photos',
        galleryContentParam: 'all',
        refreshRecentPhotosButton: '#refresh_recent_photos',
        refreshRecentPhotosDiv: '#recent_photo',
        nextMediaButton: '#next_media',
        previousMediaButton: '#prev_media',
        contentDiv: '#image_content',
        mediaBlock: '#media_',
        photoRemove: '.photo-remove',
        nextMedia: null,
        previousMedia: null,
        all_loaded: 0,
        loading_status: 0,
        lang_delete_confirm: 'Are you sure you want to delete this file?',
        post_data: {},
        windowObj: null
    };

    var xhr_load_content = null;
    var _p = {};
    var _self = this;


    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);

        if (!_self.properties.windowObj) {
            _self.properties.windowObj = new loadingContent({
                loadBlockSize: 'big'
            });
        }

        _self.init_links();
        _self.init_global_events();
        _self.init_item_block_info();
    };

    this.uninit = function () {
        $(window).unbind('scroll.media').off('changeParam.gallery');
        $('#' + _self.properties.galleryContentDiv).off();
        $(_self.properties.galleryPageButton).unbind();
        $(_self.properties.filterObj).find('li').unbind();
        $(_self.properties.albumSelectorContainer).off();
        $('#' + _self.properties.sorterId).off();
        $(_self.properties.contentDiv)
            .off('click', _self.properties.nextMediaButton)
            .off('click', _self.properties.previousMediaButton);
    };

    this.init_item_block_info = function () {
        $('#' + _self.properties.galleryContentDiv)
            .off('click', '[data-click="view-media-photo"]').on('click', '[data-click="view-media-photo"]', function (
            event) {
            event.preventDefault();
            var id_product = $(this).data('id-product') ? $(this).data('id-product') : _self.properties.idProduct;
            var id_media = $(this).data('id-media');
            var media_type = 'photo';
            _self.display_edit_view_form(id_product, id_media, media_type);
            return false;
        })
            .off('click', '[data-click="view-media-video"]').on('click', '[data-click="view-media-video"]', function (
            event) {
            event.preventDefault();
            var id_product = $(this).data('id-product') ? $(this).data('id-product') : _self.properties.idProduct;
            var id_media = 0;
            var media_type = 'video';
            _self.display_edit_view_form(id_product, id_media, media_type);
            return false;
        })
            .off('click', '.delete-media').on('click', '.delete-media', function (
            event) {
            event.preventDefault();
            var href = $(this).attr('href');
            var ajax_link = href.replace(_self.properties.deleteMediaUrl, _self.properties.deleteMediaUrlReplace);
            alerts.show({
                text: _self.properties.lang_delete_confirm,
                type: 'confirm',
                ok_callback: function () {
                    _self.delete_media(ajax_link);
                }
            });
            return false;
        })
            .off('mouseenter', '.photo').on('mouseenter', '.photo', function () {
            $(this).find('.info').stop().slideDown(100);
        })
            .off('mouseleave', '.photo').on('mouseleave', '.photo', function () {
            $(this).find('.info').stop(true).delay(100).slideUp(100);
        });
    };

    this.init_links = function () {
        $(_self.properties.galleryPageButton).unbind('click').bind('click', function () {
            _self.next_page();
        });
        $(_self.properties.refreshRecentPhotosButton).unbind('click').bind('click', function () {
            _self.refresh_recent_photos();
        });
        $(_self.properties.contentDiv).off('click', _self.properties.nextMediaButton).on('click', _self.properties.nextMediaButton, function () {
            _self.display_next_media();
        }).off('click', _self.properties.previousMediaButton).on('click', _self.properties.previousMediaButton, function () {
            _self.display_previous_media();
        });

    };

    this.init_sorter = function () {
        $('#' + _self.properties.sorterId).off('change', 'select').on('change', 'select', function () {
            _self.load_content(1, true);
        });
        $('#' + _self.properties.sorterId).off('click', '[data-role="sorter-dir"]').on('click', '[data-role="sorter-dir"]', function () {
            if ($(this).hasClass('up')) {
                $(this).removeClass('up').addClass('down');
            } else {
                $(this).removeClass('down').addClass('up');
            }
            _self.load_content(1, true);
        });
    };

    this.init_global_events = function () {
        $(window).off('changeParam.gallery').on('changeParam.gallery', function (
            e,
            param) {
            _self.properties.galleryContentParam = param;
        });
    };

    this.get_post_data = function () {
        var data = $.extend(_self.properties.post_data, {order: _self.properties.order, direction: _self.properties.direction, gallery_name: _self.properties.gallery_name, place: _self.properties.place});
        if ($('#' + _self.properties.sorterId).size()) {
            data.order = $('#' + _self.properties.sorterId).find('select').val();
            data.direction = $('#' + _self.properties.sorterId).find('[data-role="sorter-dir"]').hasClass('up') ? 'asc' : 'desc';
        }
        return data;
    };

    this.next_page = function () {
        _self.properties.galleryContentPage++;
        _self.load_content();

    };

    this.display_edit_view_form = function (id_product, id_media, media_type) {
        var data = _self.get_post_data();
        $.ajax({
            url: _self.properties.siteUrl + _self.properties.viewMediaUrl + '/' + media_type + '/' + id_product + '/' + id_media,
            type: 'POST',
            data: data,
            dataType: "html",
            cache: false,
            success: function (data) {
                _self.properties.windowObj.changeTemplate('gallery');
                _self.properties.windowObj.update_css_styles({width: '962px'});
                _self.properties.windowObj.show_load_block(data);
                if (id_media == 0) {
                    $(_self.properties.previousMediaButton).hide();
                }
                if (id_media == (parseInt(_self.properties.count_photo) - 1)) {
                    $(_self.properties.nextMediaButton).hide();
                }
                
                var view_media_close = $('#view_media_close');
                if (view_media_close.length > 0) {
                    view_media_close.bind('click', function() {
                        _self.properties.windowObj.hide_load_block();
                    });
                }
            }
        });
    };

    this.delete_media = function (url) {
        var data = _self.get_post_data();
        $.ajax({
            url: url,
            type: 'POST',
            data: data,
            dataType: 'json',
            cache: false,
            success: function (resp) {
                if (resp.status == 1) {
                    _self.reload();
                    error_object.show_error_block(resp.message, 'success');

                    if (resp.data && resp.data.albums_select) {
                        $(_self.properties.albumSelectorContainer).html(resp.data.albums_select);
                    }
                } else {
                    error_object.show_error_block(resp.message, 'error');
                }
            }
        });
    };

    this.show_content = function (content) {
        _self.properties.windowObj.changeTemplate('gallery');
        _self.properties.windowObj.update_css_styles({width: '962px'});
        _self.properties.windowObj.show_load_block(content);
    };

    this.reload = function () {
        _self.properties.galleryContentPage = 1,
            _self.properties.all_loaded = 0;
        _self.load_content(1);
    };

    this.load_content = function (is_new, force) {
        force = force || false;
        if (force) {
            if (xhr_load_content) {
                xhr_load_content.abort();
            }
            _self.properties.galleryContentPage = 1;
        } else if (_self.properties.all_loaded == 1 || _self.properties.loading_status) {
            return true;
        }
        _self.properties.loading_status = 1;
        var data = _self.get_post_data();

        xhr_load_content = $.ajax({
            url: _self.properties.siteUrl + _self.properties.galleryContentUrl + '/' + _self.properties.idProduct + '/' + _self.properties.galleryContentParam + '/' + _self.properties.photoSize,
            type: 'POST',
            data: data,
            dataType: "json",
            cache: false,
            success: function (resp) {
                if (is_new) {
                    $('#' + _self.properties.galleryContentDiv).html(resp.content);
                    $('textarea').val('');
                } else {
                    $('#' + _self.properties.galleryContentDiv).append(resp.content);
                }

                if (!resp.have_more) {
                    _self.properties.all_loaded = 1;
                    $(_self.properties.galleryPageContent).hide();
                } else {
                    $(_self.properties.galleryPageContent).show();
                }
            },
            complete: function () {
                _self.properties.loading_status = 0;
                if (_self.properties.nextMedia == 0) {
                    $(_self.properties.nextMediaButton).hide();
                }
                if (_self.properties.previousMedia == 0) {
                    $(_self.properties.previousMediaButton).hide();
                }
                $(_self.properties.photoRemove).show();
            }
        });
    };

    this.set_properties = function (properties) {
        _self.properties = $.extend(_self.properties, properties);
    };

    this.refresh_recent_photos = function () {
        $.ajax({
            url: _self.properties.siteUrl + _self.properties.recentMediaUrl,
            type: 'POST',
            data: {count: 16, upload_gid: 'photo'},
            dataType: "html",
            cache: false,
            success: function (data) {
                $(_self.properties.refreshRecentPhotosDiv).html(data);
            }
        });
    };

    this.display_next_media = function () {
        $(_self.properties.nextMediaButton).show();
        if (_self.properties.mediaId < (parseInt(_self.properties.count_photo) - 1)) {
            _self.properties.mediaId = parseInt(_self.properties.mediaId) + 1;
            $(_self.properties.mediaBlock + _self.properties.mediaId).click();
        } else {
            $(_self.properties.nextMediaButton).hide();
        }
    };

    this.display_previous_media = function () {
        $(_self.properties.previousMediaButton).show();
        if (_self.properties.mediaId > 0) {
            _self.properties.mediaId = parseInt(_self.properties.mediaId) - 1;
            $(_self.properties.mediaBlock + _self.properties.mediaId).click();
        } else {
            $(_self.properties.previousMediaButton).hide();
        }
    };

    _self.Init(optionArr);
}
