if (typeof MultiRequest !== 'undefined') {
	MultiRequest.initAction({
		gid: 'user_associations_item',
		params: {module: 'associations', model: 'Associations_model', method: 'get_new_associations'},
		paramsFunc: function() {
			return {};
		},
		callback: function(resp) {
                    if(resp.count) {
                        $('.' + resp.gid + '_count').html(resp.count);
                    } else {
                        $('.' + resp.gid + '_count').html('');
                    }
                        
		},
		period: 12,
		status: 1
	});
}
