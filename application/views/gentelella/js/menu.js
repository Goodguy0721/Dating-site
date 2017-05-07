    $('#menu_toggle').click(function () {
        if ($('body').hasClass('nav-md')) {
            $('body').removeClass('nav-md');
            $('body').addClass('nav-sm');
            $('.left_col').removeClass('scroll-view');
            $('.left_col').removeAttr('style');
            $('.sidebar-footer').hide();

            var slide = 0;
            var start_slide = 0;
            var max_slide = 0;
            var offset = 0;
            max_slide = $(window).height() - $('#left_col .left_col').height();
            $('#left_col').on('touchstart', function(e){
//              e.preventDefault();
                start_slide = e.originalEvent.touches[0].clientY;
            }).css({'position': 'absolute', 'top': 0, 'bottom': 0});
            $('#left_col').on('touchmove', function(e){
                e.preventDefault();
                slide = e.originalEvent.touches[0].clientY;
                if (start_slide < slide) {
                    offset = offset + 10;
                } else if (start_slide > slide) {
                    offset = offset - 10;
                } else {
                }
                if (offset < max_slide) {
                    offset = max_slide;
                }
                if (offset > 0) {
                    offset = 0;
                }
                start_slide = slide;
                $(this).css({top: offset + 'px'});
            });
            $('#left_col').on('wheel', function(e){
                e.preventDefault();
                slide = slide - e.originalEvent.deltaY;
                if (slide < max_slide) {
                    slide = max_slide;
                }
                if (slide > 0) {
                    slide = 0;
                }
                $(this).css({top: slide + 'px'});
            });
            if ($('#sidebar-menu li').hasClass('active')) {
                $('#sidebar-menu li.active').addClass('active-sm');
                $('#sidebar-menu li.active').removeClass('active');
            }

            if (typeof Storage != 'undefined') {
                localStorage.setItem('menuToggle', 'collapsed');
            }
        } else {
            $('#left_col').css({'position': 'relative'}).off('wheel');
            $('body').removeClass('nav-sm');
            $('body').addClass('nav-md');
            $('.sidebar-footer').show();

            if ($('#sidebar-menu li').hasClass('active-sm')) {
                $('#sidebar-menu li.active-sm').addClass('active');
                $('#sidebar-menu li.active-sm').removeClass('active-sm');
            }

            if (typeof Storage != 'undefined') {
                localStorage.setItem('menuToggle', 'expanded');
            }
        }
    });

    if (typeof Storage != 'undefined') {
        if (localStorage.getItem('menuToggle') == 'collapsed') {
            $('#menu_toggle').trigger('click');
        }
    }
