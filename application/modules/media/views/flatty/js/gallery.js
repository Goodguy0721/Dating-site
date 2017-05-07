function gallery(optionArr) {
    this.properties = {
        singleton: 1,
        site_url: '',
        get_list_url: 'media/ajax_get_gallery_list/',
        id: 'gallery',
        id_filters: 'gallery_filters',
        id_albums: 'gallery_albums',
        id_albums_select: 'album_id',
        id_sorter: 'gallery_media_sorter',
        column_width: 95,
        row_height: 305,
        columns_per_line: 9,
        lines_per_req: 6,
        margins: 5,
        button_title: 'More...',
        onInit: function () {
        },
        load_on_scroll: true,
        id_album: 0,
        album_page: 1,
        param: 'all',
        random_float: false,
        post_data: {filter_duplicate: 1},
        line_data: [],
        errorObj: new Errors,
        windowObj: new loadingContent({loadBlockWidth: '500px', closeBtnClass: 'w', loadBlockTopType: 'top', loadBlockTopPoint: 20, blockBody: true})
    };

    this.properties.blocks = {
        block8: {
            columns: 2,
            rnd_weight: 3,
            images: ['big']
        },
    };

    var _p = {
        blocks_count: 0,
        all_loaded: false,
        loading_status: false,
        img_n: 0,
        loaded_count: 0,
        load_lines_xhr: null,
        load_albums_xhr: null
    };

    var _self = this;
    $.extend(_self.properties, optionArr);


    this.init = function () {
        _p.blocks_count = 0;
        _p.all_loaded = false;
        for (var i in _self.properties.blocks) {
            if (_self.properties.blocks.hasOwnProperty(i))
                _p.blocks_count++;
        }
        $('#' + _self.properties.id).html('');
        _self.clear_gallery();
        _self.init_objects();
        _self.init_filters();
        _self.init_sorter();

        if (_self.properties.load_on_scroll) {
            setTimeout(function () {
                _self.setLoadOnScroll(1, 1);
            }, 500);
        }

        _self.properties.onInit();

        return this;
    };

    this.uninit = function () {
        _p.all_loaded = false;
        _p.loading_status = false;
        _p.img_n = 0;
        _p.loaded_count = 0;
        _p.load_lines_xhr = null;
        $(window).unbind('scroll.gallery');
        $('#' + _self.properties.id)
                .off('click', '.button-cont input[type="button"]')
                .off('mouseenter', '.block-item')
                .off('mouseleave', '.block-item')
                .off('click', '.block-item img, .zoom-icon');
        _self.albums_obj.off('change', '#' + _self.properties.id_albums_select);
        _self.sorter_obj.off();
    };

    this.init_objects = function () {
        _self.filters_obj = $('#' + _self.properties.id_filters);
        _self.albums_obj = $('#' + _self.properties.id_albums);
        _self.albums_select_obj = $('#' + _self.properties.id_albums_select);
        _self.sorter_obj = $('#' + _self.properties.id_sorter);
        var wrapper_width = _self.properties.columns_per_line * (_self.properties.column_width + _self.properties.margins * 2);
        $('#' + _self.properties.id)
                .append('<div class="wrapper"></div>')
                .append('<div class="button-cont hide"><input class="btn btn-secondary" type="button" value="' + _self.properties.button_title + '" /></div>')
                .off('click', '.button-cont input[type="button"]').on('click', '.button-cont input[type="button"]', function () {
            _self.load();
        })
                .off('mouseenter', '.block-item').on('mouseenter', '.block-item', function () {
            $(this).find('.block-item-info').slideDown(100);
        })
                .off('mouseleave', '.block-item').on('mouseleave', '.block-item', function () {
            $(this).find('.block-item-info').stop(true).delay(100).slideUp(100);
        })
                .on('mouseenter', '.block-item', function () {
                    $(this).find('.block-item-top-info').slideDown(100);
                })
                .on('mouseleave', '.block-item', function () {
                    $(this).find('.block-item-top-info').stop(true).delay(100).slideUp(100);
                })
                .off('click', '.block-item img, .zoom-icon').on('click', '.block-item img, .zoom-icon', function () {
            var img_obj = $(this).get(0).tagName === 'IMG' ? $(this) : $(this).parents('.block-item').find('img');
            var src = $(img_obj).attr('src');
            src = src.replace('gal_big_thumb_', '').replace('big_thumb_', '').replace('thumb_', '');
        });
    };

    this.init_filters = function () {
        _self.albums_obj.off('change', '#' + _self.properties.id_albums_select).on('change', '#' + _self.properties.id_albums_select, function () {
            _self.properties.id_album = $(this).val();
            if (_self.properties.id_album > 0) {
                _self.sorter_obj.stop(true).fadeIn();
            } else {
                _self.sorter_obj.hide();
            }
            _self.reload();
        });

    };

    this.init_sorter = function () {
        _self.sorter_obj.off('change', 'select').on('change', 'select', function () {
            _self.reload();
        });
        _self.sorter_obj.off('click', '[data-role="sorter-dir"]').on('click', '[data-role="sorter-dir"]', function () {
            if ($(this).hasClass('fa-arrow-up')) {
                $(this).removeClass('fa-arrow-up').addClass('fa-arrow-down');
            } else {
                $(this).removeClass('fa-arrow-down').addClass('fa-arrow-up');
            }
            _self.reload();
        });
        return this;
    };

    this.clear_gallery = function () {
        _self.properties.album_page = 1;
        _p.all_loaded = false;
        _p.loading_status = false;
        _p.loaded_count = 0;
        if (_p.load_lines_xhr) {
            _p.load_lines_xhr.abort();
        }
        if (_p.load_albums_xhr) {
            _p.load_albums_xhr.abort();
        }
        return this;
    };

    this.reload = function () {
        _self.clear_gallery();
        _self.load({}, true, true);
        return this;
    };

    this.get_post_data = function () {
        var data = $.extend(_self.properties.post_data, {order: 'date_add', direction: 'desc'});
        data.order = _self.sorter_obj.find('select').val();
        data.direction = _self.sorter_obj.find('[data-role="sorter-dir"]').hasClass('fa-arrow-up') ? 'asc' : 'desc';
        return data;
    };

    this.setLoadOnScroll = function (load_on_scroll, is_init) {
        is_init = is_init || 0;
        if (load_on_scroll && (!_self.properties.load_on_scroll || is_init)) {
            _self.properties.load_on_scroll = true;
            $(window).scrollTop(0);
            $(window).bind('scroll.gallery', function () {
                var offset = $('#' + _self.properties.id).find('.button-cont').offset();
                var window_height = window.innerHeight ? window.innerHeight : $(window).height();
                if (offset && $(window).scrollTop() >= offset.top - window_height + 100) {
                    _self.load();
                }
            });
        } else {
            _self.properties.load_on_scroll = false;
            $(window).unbind('scroll.gallery');
        }
        return this;
    };


    this.load = function (params, force, is_new) {
        params = params || {};
        force = force || false;
        is_new = is_new || false;
        params = $.extend(params, _self.get_post_data());
        _self.properties.param = _self.filters_obj.find('.active').attr('data-param');
        $(window).trigger('changeParam.gallery', _self.properties.param);
        if (_self.properties.param === 'albums' && _self.properties.id_album == 0) {
            _self.loadAlbums(params, force, is_new);
        } else {
            _self.loadLines(params, force, is_new);
        }
        return this;
    };


    this.loadAlbums = function (params, force, is_new) {
        params = params || {};
        force = force || false;
        is_new = is_new || false;
        if (force) {
            if (_p.load_albums_xhr) {
                _p.load_albums_xhr.abort();
            }
            _p.loading_status = false;
        }
        if (_p.all_loaded || _p.loading_status) {
            return this;
        }
        _p.loading_status = true;

        var preloader = (typeof PreloaderAnimation !== 'undefined') ? new PreloaderAnimation({
            selector: '#' + _self.properties.id}) : false;
        if (is_new) {
            $('#' + _self.properties.id).animate({opacity: 0.2}, 200);
        }

        params = $.extend({page: _self.properties.album_page}, params);

        _p.load_albums_xhr = $.ajax({
            type: 'POST',
            url: site_url + _self.properties.get_list_url + _self.properties.param + '/' + _self.properties.id_album,
            data: params,
            success: function (resp) {
                if (resp.errors) {
                    _self.properties.errorObj.show_error_block(resp.errors, 'error');
                } else {
                    if (is_new) {
                        $('#' + _self.properties.id).find('.wrapper').html('');
                        $('#' + _self.properties.id).find('.button-cont').hide();
                    }
                    _self.properties.album_page++;
                    _p.all_loaded = !resp.gallery.have_more;
                    _self.albums_select_obj.val(_self.properties.id_album).prop('selected', 'selected');
                    $('#' + _self.properties.id).find('.wrapper').append(resp.gallery.content);
                    if (_p.all_loaded) {
                        _self.properties.load_on_scroll = false;
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.log(errorThrown);
                }
            },
            complete: function () {
                _p.loading_status = false;
                if (preloader)
                    preloader.uninit();
                if (_p.all_loaded) {
                    $('#' + _self.properties.id).find('.button-cont').hide();
                } else {
                    $('#' + _self.properties.id).find('.button-cont').show();
                }
                $('#' + _self.properties.id).stop(true).animate({opacity: 1}, 50);
            },
            dataType: 'json',
            backend: 0
        });
        return this;
    };


    this.loadLines = function (params, force, is_new) {
        params = params || {};
        force = force || false;
        is_new = is_new || false;
        if (force) {
            if (_p.load_lines_xhr) {
                _p.load_lines_xhr.abort();
            }
            _p.loading_status = false;
        }
        if (_p.all_loaded || _p.loading_status) {
            return this;
        }
        _p.loading_status = true;
        var lines_html = '';
        var lines_obj;
        var lines_images = [];
        var line;
        _p.img_n = 0;
        //создает массив lines_obj с указанным в lines_per_req кол-вом линий (дивов)
        _self.properties.line_data = [];
        for (var i = 0; i < _self.properties.lines_per_req; i++) {
            line = _self.generateLine(1, 0);
            lines_html += line.data;
            for (var k = 0; k < line.images.length; k++) {
                lines_images.push(line.images[k]);
            }
        }
        lines_obj = $('<div>' + lines_html + '</div>');

        var preloader = (typeof PreloaderAnimation !== 'undefined') ? new PreloaderAnimation({selector: '#' + _self.properties.id}) : false;
        if (is_new) {
            $('#' + _self.properties.id).animate({opacity: 0.2}, 200);
        }

        var post_data = $.extend({icons: lines_images, loaded_count: _p.loaded_count, line_data: _self.properties.line_data}, params);

        _p.load_lines_xhr = $.ajax({
            type: 'POST',
            url: site_url + _self.properties.get_list_url + _self.properties.param + '/' + _self.properties.id_album,
            data: post_data,
            success: function (resp) {
                if (resp.errors) {
                    _self.properties.errorObj.show_error_block(resp.errors, 'error');
                    _p.all_loaded = true;
                    _p.loading_status = true;
                } else {
                    if (is_new) {
                        $('#' + _self.properties.id).find('.wrapper').html('');
                        $('#' + _self.properties.id).find('.button-cont').hide();
                    }
                    _p.all_loaded = !resp.gallery.have_more;
                    if (resp.gallery.media_count && resp.gallery.media) {
                        _p.loaded_count += resp.gallery.media_count;
                        if (_p.all_loaded) {
                            for (var n = resp.gallery.media_count; n < resp.gallery.requested_count; n++) {
                                $(lines_obj).find('img[data-num="' + n + '"]').parent('.block-item').remove();
                            }
                            $(lines_obj).find('.block:empty').remove();
                            $(lines_obj).find('.gallery-line:empty').remove();
                            $(lines_obj).find('.gallery-line:last').find('.block').addClass('fleft');
                        }
                        $('#' + _self.properties.id).find('.wrapper').append(resp.content);
                        $('.block-item').css('margin', _self.properties.margins + "px");
                    } else if (resp.gallery.msg) {
                        $('#' + _self.properties.id).find('.wrapper').append('<div>' + resp.gallery.msg + '</div>');
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                if (typeof (console) !== 'undefined') {
                    console.log(errorThrown);
                }
            },
            complete: function () {
                _p.loading_status = false;
                if (preloader)
                    preloader.uninit();
                if (_p.all_loaded) {
                    $('#' + _self.properties.id).find('.button-cont').hide();
                } else {
                    $('#' + _self.properties.id).find('.button-cont').show();
                }
                $('#' + _self.properties.id).stop(true).animate({opacity: 1}, 50);
            },
            dataType: 'json',
            backend: 0
        });
        return this;
    };


    this.generateLine = function (with_html, html_as_obj) {
        with_html = with_html || 0;
        html_as_obj = html_as_obj || 0;
        var free_columns = _self.properties.columns_per_line;
        var result_blocks = [];
        var line_images = [];
        while (free_columns > 0) {
            var rnd_weights = [];
            var allowed_blocks = [];
            for (var i in _self.properties.blocks) {
                if (_self.properties.blocks.hasOwnProperty(i) && _self.properties.blocks[i].columns <= free_columns) {
                    allowed_blocks.push(_self.properties.blocks[i]);
                    for (var k = 0; k < _self.properties.blocks[i].rnd_weight; k++) {
                        rnd_weights.push(allowed_blocks.length - 1);
                    }
                }
            }
            if (rnd_weights.length) {
                var rnd = Math.floor((Math.random() * rnd_weights.length));
                result_blocks.push(allowed_blocks[rnd_weights[rnd]]);
                for (var i = 0; i < allowed_blocks[rnd_weights[rnd]].images.length; i++) {
                    line_images.push(allowed_blocks[rnd_weights[rnd]].images[i]);
                }
                free_columns -= allowed_blocks[rnd_weights[rnd]].columns;
            } else {
                free_columns = 0;
            }
        }
        if (with_html) {
            return {data: _self.getLineHtml(result_blocks, html_as_obj), images: line_images};
        } else {
            return {data: result_blocks, images: line_images};
        }
    };


    this.getLineHtml = function (blocks, html_as_obj) {
        html_as_obj = html_as_obj || 0;
        var block_width, block_items;
        var block_html = '';
        for (var i = 0; i < blocks.length; i++) {
            block_items = '';
            block_width = blocks[i].columns * _self.properties.column_width + ((blocks[i].columns) * _self.properties.margins * 2);
            for (var k = 0; k < blocks[i].images.length; k++) {
                block_items += '<div class="block-item fleft ' + blocks[i].images[k] + '" style="margin: ' + _self.properties.margins + 'px"><img data-num="' + _p.img_n + '" class="' + blocks[i].images[k] + '" /><div class="block-item-info info"></div><div class="block-item-top-info info"></div></div>';
                _p.img_n++;
            }
            if (_self.properties.random_float) {
                var rnd_float = Math.random() > 0.5 ? 'left' : 'right';
                block_html += '<div class="block" style="width: ' + block_width + 'px; float: ' + rnd_float + ';">' + block_items + '</div>';
            } else {
                block_html += '<div class="block fleft" style="width: ' + block_width + 'px;">' + block_items + '</div>';
            }

            _self.properties.line_data.push({count: blocks[i].images.length, width: block_width});
        }
        _self.properties.line_data.push({end_block: 1});
        var line_html = '<div class="gallery-line oh">' + block_html + '</div>';

        if (html_as_obj) {
            return $(line_html);
        } else {
            return line_html;
        }
    };

}
