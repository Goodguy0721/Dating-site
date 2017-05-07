if (typeof MultiRequest !== 'undefined') {
	MultiRequest.initAction({
		gid: 'mailbox_request',
		params: {module: 'mailbox', model: 'Mailbox_model', method: 'get_request_notifications'},
		paramsFunc: function() {
            return {};
        },
		callback: function(resp) {
			if (resp) {
				for (var i in resp.notifications) {
					var options = {
						title: resp.notifications[i].title,
						text: resp.notifications[i].text,
						image: resp.notifications[i].user_icon,
						image_link: resp.notifications[i].user_link,
						sticky: false,
						time: 15000,
						link: resp.notifications[i].user_link,
						more: resp.notifications[i].more
					};
					notifications.show(options);
				}
                                
                                if(resp.new_message_alert_html) {
                                    var mailboxBlock = $('#menu_mailbox_alerts');
                                    mailboxBlock.find('.menu-alerts-more-items').html(resp.new_message_alert_html); 
                                    
                                    if(resp.inbox_new_message > resp.max_messages_count) {
                                        mailboxBlock.find('.menu-alerts-view-all').removeClass('hide');
                                    } else {
                                        mailboxBlock.find('.menu-alerts-view-all').addClass('hide');
                                    }
                                }
                                
				$('.inbox_new_message').html(resp.inbox_new_message);
				$('.ind_inbox_new_message').html('('+resp.inbox_new_message+')');
				if (resp.inbox_new_message) {
					$('.ind_inbox_new_message').show();
				} else {
					$('.ind_inbox_new_message').hide();
				}
			}
		},
		period: 12,
		status: 1
	});
}
