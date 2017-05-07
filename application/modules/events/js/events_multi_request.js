if (typeof MultiRequest !== 'undefined') {
	MultiRequest.initAction({
		gid: 'events_request',
		params: {module: 'events', model: 'Events_model', method: 'get_request_notifications'},
		paramsFunc: function() {
			return {};
		},
		callback: function(resp) {
			if (resp) {
                            if(resp.events.length) {
				for (var i in resp.events) {
                                    if(resp.events[i].is_new) {
					var req = resp.events[i];
					var options = {
						title: req.title,
						text: req.text,
						//image: req.user_icon,
						//image_link: req.user_link,
						sticky: true,
						time: 15000
					};
					notifications.show(options);                                        
                                    }
				}   
                                $('.event_requests_count').html(resp.events.length);
                            }
			}
		},
		period: 12,
		status: 1
	});
}
