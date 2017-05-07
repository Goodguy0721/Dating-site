function clubsMedia(optionArr) {
    this.properties = {
        gallery_name: 'club_mediagallery',
        siteUrl: '/',
        idGroup: 0,
        viewMediaUrl: 'clubs/ajaxViewMedia',
        galleryContentDiv: 'gallery_content',
        galleryContentUrl: 'clubs/ajaxMedia',
        galleryContentParam: 'all',
        galleryContentPage: 1,
        galleryPageContent: '.media-button-content',
        galleryPageButton: '#media_button',
        filterObj: '#filters',
        sorterId: 'media_sorter',
        load_on_scroll: 0,
        all_loaded: 0,
        loading_status: 0,
        order: 'date_add',
        direction: 'desc',
        post_data: {},

        mediaId: 0,
        imageContentDiv: '#image_content',
        nextMediaButton: '#next_media',
        previousMediaButton: '#prev_media',
        mediaPosition: '#media_position',
        nextMedia: null,
        previousMedia: null,
        precache_images: true,
        rand_param: null,

        windowObj: null,
    };

    var xhr_load_content = null;
    var xhr_load_view_content = null;
    var _p = {};

    var _self = this;


    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);
        
        if (!_self.properties.windowObj) {
            _self.properties.windowObj = new loadingContent({
                loadBlockWidth: '500px', 
                closeBtnClass: 'w', 
                loadBlockTopType: 'top', 
                loadBlockTopPoint: 20, 
                blockBody: true, 
                showAfterImagesLoad: false
            });
        }
 
        _self.init_links();
        _self.init_filters();
        _self.init_sorter();
        _self.init_global_events();
        
        if (_self.properties.load_on_scroll) {
            setTimeout(function () {
                _self.setLoadOnScroll();
            }, 500);
        }
        _self.init_item_block_info();
    };

    this.uninit = function () {
        $(window).unbind('scroll.media').off('changeParam.gallery');
        $('#' + _self.properties.galleryContentDiv).off();
        $(_self.properties.galleryPageButton).unbind();
        $(_self.properties.filterObj).find('li').unbind();
        $('#' + _self.properties.sorterId).off();
    };

    this.init_item_block_info = function () {
        $('#' + _self.properties.galleryContentDiv).off('click', '[data-click="view-media"]').on('click', '[data-click="view-media"]', function (event) {
            event.preventDefault();
            _self.properties.idGroup = $(this).data('club-id') || _self.properties.idGroup;
            var id_media = $(this).data('id-media');
            _self.display_edit_view_form(id_media);
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
    };

    this.init_filters = function () {
        $(_self.properties.filterObj).find('li').each(function () {
            var param = $(this).attr('data-param');
            if (_self.properties.galleryContentParam == param) {
                $(this).addClass('active');
            }
            $(this).bind('click', function () {
                $(_self.properties.filterObj).find('li').removeClass('active');
                $(this).addClass('active');
                var param = $(this).attr('data-param');
                $('#' + _self.properties.sorterId).stop(true).fadeIn();
                _self.properties.galleryContentParam = param;
                _self.reload();
            });
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
        $(window).off('changeParam.gallery').on('changeParam.gallery', function (e, param) {
            _self.properties.galleryContentParam = param;
        });
    };

    this.get_post_data = function () {
        var data = $.extend(_self.properties.post_data, {order: _self.properties.order, direction: _self.properties.direction, gallery_name: _self.properties.gallery_name});
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

    this.display_edit_view_form = function (id_media) {
        _self.properties.windowObj.changeTemplate('gallery');
        _self.properties.windowObj.update_css_styles({width: '962px'});
        _self.properties.windowObj.show_load_block('<div class="media-gallery-content" id="image_content"></div>');

        _self.properties.mediaId = id_media;
        _self.initViewContent();
        _self.initViewButtons();
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
            url: _self.properties.siteUrl + _self.properties.galleryContentUrl + '/' + _self.properties.idGroup + '/' + _self.properties.galleryContentParam + '/' + _self.properties.galleryContentPage,
            type: 'POST',
            data: data,
            dataType: "json",
            cache: false,
            success: function (resp) {
                if (is_new) {
                    $('#' + _self.properties.galleryContentDiv).html(resp.content);
                } else {
                    $('#' + _self.properties.galleryContentDiv).append(resp.content);
                }

                if (!resp.have_more) {
                    _self.properties.load_on_scroll = 0;
                    _self.properties.all_loaded = 1;
                    $(_self.properties.galleryPageContent).hide();
                } else {
                    $(_self.properties.galleryPageContent).show();
                }
            },
            complete: function () {
                _self.properties.loading_status = 0;
            }
        });
    };

    this.set_properties = function (properties) {
        _self.properties = $.extend(_self.properties, properties);
    };

    this.setLoadOnScroll = function () {
        if (_self.properties.load_on_scroll) {
            $(window).scrollTop(0);
            $(window).bind('scroll.media', function () {
                var offset = $(_self.properties.galleryPageContent).offset();
                var window_height = window.innerHeight ? window.innerHeight : $(window).height();
                if (offset && $(window).scrollTop() >= offset.top - window_height + 100) {
                    _self.next_page();
                }
            });
        } else {
            $(window).unbind('scroll.media');
        }
    };

    // View Media
    this.initViewButtons = function () {
        $(_self.properties.imageContentDiv).off('click', _self.properties.nextMediaButton).on('click', _self.properties.nextMediaButton, function () {
            _self.displayNextMedia();
        }).off('click', _self.properties.previousMediaButton).on('click', _self.properties.previousMediaButton, function () {
            _self.displayPreviousMedia();
        });
    };

    this.initViewContent = function () {
        if (xhr_load_view_content && xhr_load_view_content.state() === 'pending') {
            return xhr_load_view_content;
        }

        var data = _self.get_post_data();
        data.rand_param = _self.properties.rand_param !== null ? _self.properties.rand_param : 0;

        $(_self.properties.imageContentDiv).stop().animate({opacity: 0.3}, 200);
        xhr_load_view_content = $.ajax({
            url: _self.properties.siteUrl + _self.properties.viewMediaUrl + '/' + 
                    _self.properties.mediaId + '/' + _self.properties.idGroup + '/' +_self.properties.galleryContentParam,
            type: 'POST',
            data: data,
            dataType: "json",
            cache: false,
            success: function (resp) {
                var height_old = $(_self.properties.imageContentDiv).height();
                var new_content = $(resp.content.trim());
                var dfd = $.Deferred();

                dfd = _p.animateLoad(height_old, new_content);

                if (resp.position_info) {
                    $(_self.properties.mediaPosition).html(resp.position_info.position + ' / ' + resp.position_info.count);
                    _self.properties.nextMedia = resp.position_info.next;
                    _self.properties.previousMedia = resp.position_info.previous;
                    if (_self.properties.precache_images) {
                        if (resp.position_info.next_image.image) {
                            var next_img = new Image();
                            next_img.src = resp.position_info.next_image.image + '?' + _self.properties.rand_param;
                            if (resp.position_info.next_image.thumb) {
                                var next_img_thumb = new Image();
                                next_img_thumb.src = resp.position_info.next_image.thumb + '?' + _self.properties.rand_param;
                            }
                        }
                        if (resp.position_info.previous_image.image) {
                            var previous_img = new Image();
                            previous_img.src = resp.position_info.previous_image.image + '?' + _self.properties.rand_param;
                            if (resp.position_info.previous_image.thumb) {
                                var previous_img_thumb = new Image();
                                previous_img_thumb.src = resp.position_info.previous_image.thumb + '?' + _self.properties.rand_param;
                            }
                        }
                    }
                }

                dfd.always(function () {
                    if (_self.properties.nextMedia == 0) {
                        $(_self.properties.nextMediaButton).hide();
                    }
                    if (_self.properties.previousMedia == 0) {
                        $(_self.properties.previousMediaButton).hide();
                    }
                });
            }
        });

        return xhr_load_view_content;
    };

    _p.animateLoad = function (height_old, new_content) {
        var dfd = $.Deferred();
        var img_obj = new_content.find('[data-image-src]');
        var img_src = img_obj.data('image-src');
        var set_content = function () {
            var height = $(_self.properties.imageContentDiv).css({visibility: 'hidden'}).html(new_content).height();
            $(_self.properties.imageContentDiv).css({height: height_old + 'px'});
            dfd.resolve(height);
        };
        if (img_src) {
            var img = new Image();
            img.src = img_src;
            if (img.height) {
                set_content();
            } else {
                $(_self.properties.imageContentDiv).html(new_content).stop(true).css({opacity: 1});
                dfd.resolve();
            }
        } else {
            $(_self.properties.imageContentDiv).html(new_content).stop(true).css({opacity: 1});
            dfd.reject();
        }
        dfd.done(function (height) {
            $(_self.properties.imageContentDiv).css({visibility: 'visible'}).stop(true).animate({height: height + 'px', opacity: 1}, 200, function () {
                $(this).css('height', 'auto');
            });
            if (typeof window[_self.properties.gallery_name] === 'object') {
                $('#' + window[_self.properties.gallery_name].properties.windowObj.properties.loadBlockBgID).animate({scrollTop: 0}, 100);
            }
        });
        return dfd;
    };

    this.displayNextMedia = function () {
        _self.properties.mediaId = _self.properties.nextMedia;
        _self.initViewContent();
    };

    this.displayPreviousMedia = function () {
        _self.properties.mediaId = _self.properties.previousMedia;
        _self.initViewContent();
    };


    _self.Init(optionArr);
}