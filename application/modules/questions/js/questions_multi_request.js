if(typeof MultiRequest !== 'undefined'){
	MultiRequest.initAction({
		gid: 'questions_request',
		params: {module: 'questions', model: 'Questions_model', method: 'getNotifications'},
		paramsFunc: function(){return {};},
		callback: function(resp){
                    if(resp){
                        if (resp.new_questions > 0) {
                            $('.new_questions').html(resp.new_questions);
                        } else {
                            $('.new_questions').html('');
                        }
                    }
		},
		period: 12,
		status: 1
	});
}
