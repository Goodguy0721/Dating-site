function clubsUsers(optionArr) {
    this.properties = {
        siteUrl: '',
        idGroup: 0,
        loadUsersUrl: 'clubs/ajaxGetUsersData/',
        loadFormUrl: 'clubs/ajaxGetUsersForm/',
        viewFormLinkId: 'js-open-users_form',
        searchObjId: 'user_search',
        userItemsId: 'users_items',
        userPagesId: 'user_page',
        contentObj: null,
    };

    var _self = this;
    var xhr_load_users = null;

    this.init = function(options){
        _self.properties = $.extend(_self.properties, options);        

        if (!_self.properties.contentObj) {
                _self.properties.contentObj = new loadingContent({
                    loadBlockWidth: '680px',
                    closeBtnPadding: 15,
                    showAfterImagesLoad: false
                });
        }

        $('#' + _self.properties.viewFormLinkId).off('click').click(function() {
            _self.openForm();
            return false;
        });
    };

    this.openForm = function(){
        var url =  _self.properties.siteUrl + _self.properties.loadFormUrl;

        $.ajax({
            url: url,
            type: 'GET',
            cache: false,
            success: function(resp) {
                _self.properties.contentObj.show_load_block(resp);
                _self.loadUsers('', 1);
                $('#' + _self.properties.searchObjId).off('keyup').keyup(function() {
                    _self.loadUsers($(this).val(), 1);
                });
            }
        });
    };

    this.loadUsers = function(search, page) {
        if (xhr_load_users && xhr_load_users.state() == 'pending') {
            return xhr_load_users;
        }
        xhr_load_users = $.ajax({
            url: _self.properties.siteUrl+_self.properties.loadUsersUrl + _self.properties.idGroup + '/' + page,
            dataType: 'json',
            type: 'POST',
            data: {search: search},
            cache: false,
            success: function(data) {
                $('#'+_self.properties.userItemsId).html('');
                for (var id in data.items) {
                    var elem = '<div class="club-users__item col-xs-6 col-sm-4 col-md-6 col-lg-3"><div class="clubs-users__media"><a href="' + data.items[id].link + '"><img src="' + data.items[id].image + '"></a></div><div class="clubs-users__media-body">';
                    elem += '<div><a href="' + data.items[id].link + '">' + data.items[id].output_name + '</a>, ' + data.items[id].age + '</div>'
                        + '<div>'+ data.items[id].location + '</div>'
                        + '</div></div>';
                    $('#'+_self.properties.userItemsId).append(elem);
                }
                _self.generateUserPages(data.pages, data.current_page, search);
            }
        });

        return xhr_load_users;
    };

    var printPages = function(from, to, current) {
        current = parseInt(current);
        for (var i = from; i <= to; i++) {
            if (i === current) {
                $('#' + _self.properties.userPagesId).append('<li class="active"><a href="#">' + i + '</a></li>');
            } else {
                $('#' + _self.properties.userPagesId).append('<li><a href="#">' + i + '</a></li>');
            }
        }
    };

    this.generateUserPages = function(pages, current_page, search) {
        $('#' + _self.properties.userPagesId + ' a').off('click');
        $('#' + _self.properties.userPagesId).empty();
        var max_pages = 12;
        if (pages > 1) {
            var range = max_pages / 2;
            var bound = pages - max_pages;
            var from = current_page > range ? current_page - range : 1;
            if (from > bound) {
                from = bound;
            }
            if (current_page > range + 1) {
                $('#' + _self.properties.userPagesId).append('<li><a href="#">1</a></li>');
                $('#' + _self.properties.userPagesId).append('<li>...</li>');
            }
            printPages(1, pages, current_page);
            if(current_page < pages - range) {
                $('#' + _self.properties.userPagesId).append('<li>...</li>');
                $('#' + _self.properties.userPagesId).append('<li><a href="#">' + pages + '</a></li>');
            }
            $('#' + _self.properties.userPagesId + ' a').click(function() {
                _self.loadUsers(search, $(this).text());
                return false;
            });
        }
    };

    _self.init(optionArr);

}
