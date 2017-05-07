function events_users(optionArr) {
    this.properties = {
        siteUrl: '',
        btnInviteAllId: 'invite_users',
        btnInviteUserClass: 'btn-invite-user',
        checkboxId: 'invite_user_',
        checkboxClass: 'events-user-checkbox',
        checkboxAllId: 'invite_user_all',
        setInviteUrl: 'events/ajaxInviteSelect',
        checkedUsersFieldId: 'checked_users',
        event_id: null,
        user_id: '',
        checkedUsersList: new Array(),
        invitedButtonText: '',
    }

    var _self = this;

    this.Init = function(options) {
        _self.properties = $.extend(_self.properties, options);
        _self.init_links();
    };

    this.init_links = function() {
        $('#' + _self.properties.btnInviteAllId).off('click').on('click', function () {
            
            var selectedUsers = new Array();
            $('.' + _self.properties.checkboxClass + ' > input:checked').each(function(key, elem){
                selectedUsers.push($(elem).val());
            });
            
            _self.properties.checkedUsersList = selectedUsers;
            _self.inviteUsers();
        });
        
        $('.' + _self.properties.btnInviteUserClass).off('click').on('click', function () {
            var user_id = $(this).attr('data-user-id');
            if(user_id) {
                _self.properties.checkedUsersList = user_id;
                _self.inviteUsers();
            }
            
        });
        
        $('.' + _self.properties.checkboxClass + ' > input').off().on('click', function () {
           _self.toggleInviteButton();
        });
        
        $('#' + _self.properties.checkboxAllId).off().on('click', function () {
           _self.checkedAll($(this).prop("checked"));
           _self.toggleInviteButton();
        });
    }
    
    this.inviteUsers = function() {
        if(_self.properties.checkedUsersList) {
            $.ajax({
                    url: _self.properties.siteUrl + _self.properties.setInviteUrl + '/' + _self.properties.event_id,
                    type: 'POST',
                    dataType : "json",
                    data: {'invited':_self.properties.checkedUsersList},
                    cache: false,
                    success: function(resp){
                        if(resp.success) {
                            error_object.show_error_block(resp.success, 'success');
                            var i;
                            for(i = 0; i < resp.data.users_ids.length; i++ ) {
                                _self.setDisabled(resp.data.users_ids[i]);
                            }
                        }
                        
                        //events.properties.contentObj.hide_load_block();
                    }
            });            
        }

    }
    
    this.checkedAll = function(checked) {
        if(checked) {
            $('.' + _self.properties.checkboxClass + ' > input:enabled').prop("checked", true);
        } else {
            $('.' + _self.properties.checkboxClass + ' > input:enabled').prop("checked", false);
        }
    }
    
    this.toggleInviteButton = function() {
        if($('.' + _self.properties.checkboxClass + ' > input:checked').length) {
            $('#' + _self.properties.btnInviteAllId).prop('disabled', false);
        } else {
            $('#' + _self.properties.btnInviteAllId).prop('disabled', true);
        }
    }
    
    this.setDisabled = function(id) {
        $('.' + _self.properties.btnInviteUserClass + '[data-user-id="' + id + '"]').prop('disabled', true).removeClass('btn-primary').val(_self.properties.invitedButtonText);
        $('#' + _self.properties.checkboxId + id).prop('disabled', true).prop('checked', false);
    }

    _self.Init(optionArr);
}


