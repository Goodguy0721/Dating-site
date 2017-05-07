if(typeof MultiRequest !== 'undefined'){
	MultiRequest.initAction({
		gid: 'tickets_request',
		params: {module: 'tickets', model: 'Tickets_model', method: 'get_request_notifications'},
		paramsFunc: function(){return {};},
		callback: function(resp){
			if(resp){
				for(var i in resp.notifications){
					var options = {
						title: resp.notifications[i].title,
						text: resp.notifications[i].text,
						sticky: false,
						time: 15000,
						link: resp.notifications[i].link,
					};
					notifications.show(options);
				}

                                if(resp.new_message_alert_html) {
                                    var mailboxBlock = $('#menu_admin_alerts');
                                    mailboxBlock.find('.menu-alerts-more-items').html(resp.new_message_alert_html); 
                                    
                                    if(resp.admin_new_message > resp.max_messages_count) {
                                        mailboxBlock.find('.menu-alerts-view-all').removeClass('hide');
                                    } else {
                                        mailboxBlock.find('.menu-alerts-view-all').addClass('hide');
                                    }
                                }
                                
				$('.admin_new_message').html(resp.admin_new_message);
			}
		},
		period: 12,
		status: 1
	});
}
