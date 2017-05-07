function loadingContent(optionArr){
    this.properties = {
        loadBlockID: 'user_load_content'/*+randNumber*/,
        loadBlockBgID: 'user_load_content_bg'/*+randNumber*/,
        loadBlockSmClass: 'modal fade bs-example-modal-sm',
        loadBlockLgClass: 'modal fade bs-example-modal-lg',
        loadContentSmClass: 'modal-sm',
        loadContentLgClass: 'modal-lg',
        loadContentId: 'modal_dialog',
        loadBlockSize: 'small',
        loadBlockTitle: '',
        dir: site_rtl_settings,             /// rtl
        posPropertyLeft: "left",
        posPropertyRight: "right",
        blockBody: true,
        
        closeBtnID: '',
        closeBtnLabel: 'Close',
        closeBtnClass: 'close',
        closeBtnUse: true,
        closeBtnPadding: 10,
        footerButtons: '',
        draggable: false,
        showAfterImagesLoad: true,
        contentImages: 0,
        contentLoadedImages: 0,
        
        onClose: function(){},
        
        destroyOnReload: true,
    }

    var _self = this;

    this.Init = function(options) {
        
        _self.properties = $.extend(_self.properties, options);
        var randNumber = Math.round(Math.random(1000)*1000);
        _self.properties.loadBlockID += randNumber;
        _self.properties.loadBlockBgID += randNumber;
        
        if (_self.properties.closeBtnID.length == 0) {
            _self.properties.closeBtnID = 'user_load_content_close' + randNumber;
        }
        
        _self.properties.loadContentId += randNumber;
        if(_self.properties.dir == 'rtl'){
            _self.properties.posPropertyLeft = "right";
            _self.properties.posPropertyRight = "left";
            
        }
        
        _self.create_load_block();
        
        $(document).on('pjax:start', function(e) {
        /*    if(_self.properties.destroyOnReload){*/
                _self.destroy();
        /*    }else{
                _self.hide_load_block();
            }*/
        });
    }

    this.extend_errors = function(errors){
        _self.errors = $.extend(_self.errors, errors);
    }
    
    this.destroy = function(){
        _self.hide_load_block();
        $("body")
            .find('#' + _self.properties.loadBlockID + ', #' + _self.properties.loadBlockBgID + ', #' + _self.properties.closeBtnID)
            .remove();
    }

    this.create_load_block = function(){
        $("body").append(
                        '<div id="' + _self.properties.loadBlockID + '_main" style="bottom: auto; width: 100%; height: 100%; overflow: auto;" tabindex="-1" role="dialog">\n\
                            <!-- div id="' + _self.properties.loadBlockBgID + '"></div -->\n\
                            <div id="' + _self.properties.loadContentId + '" class="modal-dialog">\n\
                                <div id="' + _self.properties.loadBlockID + '" style="max-width: 100%;">\n\
                                    <div id="' + _self.properties.loadBlockID + '_header"' + (!_self.properties.loadBlockTitle ? ' class="no-header"' : '') +'>\n\
                                        <h4 class="modal-title" id="myModalLabel">' + _self.properties.loadBlockTitle + '</h4>\n\
                                    </div>\n\
                                    <!-- div id="' + _self.properties.closeBtnID + '" class="load_content_close">\n\
                                        <i class="fa fa-close fa-lg"></i>\n\
                                    </div -->\n\
                                    <div id="' + _self.properties.loadBlockID + '_content"></div>\n\
                                    <div id="' + _self.properties.loadBlockID + '_footer"></div>\n\
                            </div></div></div></div>'
                        );
        $("#" + _self.properties.loadBlockID + '_header').addClass('modal-header');
        $("#" + _self.properties.loadBlockID + '_content').addClass('modal-body');
        $("#" + _self.properties.loadBlockID + '_footer').addClass('modal-footer');
        $("#" + _self.properties.loadBlockID).addClass('modal-content');
        if (_self.properties.loadBlockSize == 'small') {
            $("#" + _self.properties.loadBlockID + '_main').addClass(_self.properties.loadBlockSmClass);
            $('#' + _self.properties.loadContentId).addClass(_self.properties.loadContentSmClass);
        } else {
            $("#" + _self.properties.loadBlockID + '_main').addClass(_self.properties.loadBlockLgClass);
            $('#' + _self.properties.loadContentId).addClass(_self.properties.loadContentLgClass);
        }
        
        $("#" + _self.properties.loadBlockID + '_footer').append(_self.properties.footerButtons);
        $("#" + _self.properties.loadBlockID + '_footer').append('<button type="button" id="' + _self.properties.closeBtnID + '" class="btn btn-default" data-dismiss="modal">' + _self.properties.closeBtnLabel + '</button>');

        if(_self.properties.draggable){
            $("#" + _self.properties.loadBlockID).draggable({ handle: "h1" });
        }

        $("#" + _self.properties.loadBlockBgID)
            .addClass('modal-backdrop fade')
            .css({
                'width': '100%',
                'height': '100%',
                'position': 'fixed',
                'top': '0',
                'left': '0',
            });

        if (_self.properties.closeBtnUse){
            _self.create_close();
        }
    }

    this.show_load_block = function(content, animate){
        animate = animate || false;
        _self.active_bg();
        var height_old = $("#" + _self.properties.loadBlockID + '_content').height();
        $("#" + _self.properties.loadBlockID + '_content').html(content);
        var height = $("#" + _self.properties.loadBlockID + '_content').height();
        if(animate && height_old && height){
            $("#" + _self.properties.loadBlockID + '_content').stop().css('height', height_old + 'px').animate({'height': height + 'px'}, 100, function(){$(this).css('height', 'auto')});
        }
        
        if(_self.properties.blockBody){
            var oldWidth = $('body').width();
            $('body').css('overflow-y', 'hidden');
            $('body').css('margin-' + _self.properties.posPropertyRight, $('body').width() - oldWidth);            
        }

        if(_self.properties.showAfterImagesLoad){
            _self.properties.contentImages = $("#" + _self.properties.loadBlockID + ' img').length;
            _self.properties.contentLoadedImages = 0;
            _self.show_after_load();
        }else{
            $("#" + _self.properties.loadBlockID + '_main').css({'display': 'block'});
            $("#" + _self.properties.loadBlockID + '_main').addClass('in');
            $("#" + _self.properties.loadBlockBgID).addClass('in');
            if(_self.properties.closeBtnUse){
                _self.active_close();    
            }
        }
    }

    this.show_after_load = function(){
        if(_self.properties.contentLoadedImages < _self.properties.contentImages){
            $("#" + _self.properties.loadBlockID + ' img').load(function(){
                _self.properties.contentLoadedImages++;
            });
            setTimeout( _self.show_after_load, 300);
        }else{
            $("#" + _self.properties.loadBlockID + '_main').css({'display': 'block'});
            $("#" + _self.properties.loadBlockID + '_main').addClass('in');
            $("#" + _self.properties.loadBlockBgID).addClass('in');
            if(_self.properties.closeBtnUse){
                _self.active_close();    
            }
        }
    }

    this.hide_load_block = function(){
        if(window.wc_videoStreamUrl){
            window.wc_videoStreamUrl = null;
        }
        if(_self.properties.blockBody){
            $('body').css('overflow-y', 'auto').css('margin-' + _self.properties.posPropertyRight, 'auto');
        }
        $("#" + _self.properties.loadBlockBgID).removeClass('in');
        if($("#" + _self.properties.loadBlockID).css('display') == 'none'){
            return;
        }
        _self.clear_buttons();
        $("#" + _self.properties.loadBlockID + '_main').css({'display': 'none'});
        $("#" + _self.properties.loadBlockID + '_main').removeClass('in');
        $("#" + _self.properties.loadBlockID + '_content').html('');
        _self.inactive_bg();
        _self.properties.onClose();
    }

    this.clear_buttons = function(){
        if(_self.properties.closeBtnUse){
            _self.inactive_close();        
        }
    }

    this.active_bg = function(){
        $("#" + _self.properties.loadBlockBgID).unbind().bind('click', function(e){
            if($(e.target).attr('id') == _self.properties.loadBlockBgID){
                _self.hide_load_block();
            }
        });
    }

    this.inactive_bg = function(){
        if($("#" + _self.properties.loadBlockBgID).css('display') != 'none'){
            $("#" + _self.properties.loadBlockBgID).unbind();
        }
    }

    this.active_close = function(){
        _self.position_close();
        $("#" + _self.properties.closeBtnID + ', #' + _self.properties.closeBtnID + '_header').bind('click', function(){
            _self.hide_load_block();
        });
    }

    this.inactive_close = function(){
        if($("#" + _self.properties.closeBtnID).css('display') != 'none'){
            $("#" + _self.properties.closeBtnID).unbind();
        }
    }

    this.create_close = function(){
        $("#" + _self.properties.loadBlockID + '_header').append(
            '<div id="' + _self.properties.closeBtnID + '_header"><i class="fa fa-close fa-lg"></i></div>'
        );
        $("#" + _self.properties.closeBtnID + '_header').addClass(_self.properties.closeBtnClass);
        $("#" + _self.properties.closeBtnID + '_header').css({
            'cursor': 'pointer',
            'position': 'absolute',
            'z-index': '1002'
        });
        _self.position_close();
    }

    this.position_close = function(){
        var padding = _self.properties.closeBtnPadding;
        $("#" + _self.properties.closeBtnID + '_header').css({'top': padding + 'px'}).css(_self.properties.posPropertyRight, padding + 'px');
    }

    this.changeTemplate = function(template) {
        return this;
    }

    this.update_css_styles = function(cssArr, target){
        target = target || 'block';
        var obj = (target == 'bg') ? $("#" + _self.properties.loadBlockBgID) : $("#" + _self.properties.loadBlockID);
        for (var prop in cssArr) {
            obj.css(prop, cssArr[prop]);
        }
        return this;
    }

    _self.Init(optionArr);
    return _self;
};
