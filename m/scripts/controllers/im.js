/*'use strict';*/

angular.module('datingMobile').controller('ImCtrl', function($rootScope, $scope, backend, Api, appHistory, Layout, Helpers) {
	$rootScope.actions = {
		text: $rootScope.l('im')
	};
	var checkService = function(data) {
		if (0 === data.im_status.im_service_access) {
            if ($rootScope.device == 'iosDevice') {
                Layout.addAlert('warning', $rootScope.l('im_disabled'), true);
                $rootScope.go('/home');
            } else {
                Layout.addAlert('warning', $rootScope.l('im_service_required'), true);
                $rootScope.go('services/view_service/im', false);
                return false;
            }
		}
		return true;
	};

	var checkList = function(data) {
		if (Helpers.isObjEmpty(data.list)) {
			Layout.addAlert('warning', $rootScope.l('im_empty_list'));
			return false;
		}
		return true;
	};

	// Contact list
	Api.query({module: 'im', method: 'get_contact_list'}).then(function(resp) {
		$scope.data = resp.data;
		if (!checkService(resp.data)) {
			return false;
		} else if (!checkList(resp.data)) {
			return false;
		}
	});

	// Update contact list
	backend.add({
		name: 'imContacts',
		data: {
			module: 'im',
			model: 'Im_contact_list_model',
			method: 'get_contact_list',
			gid: 'im_get_contact_list',
			loaded_contact_ids: ''
		},
		callback: function(resp) {
			if ($scope.data !== resp) {
				$scope.data = resp;
			}
		}
	});
}).controller('ImChatCtrl', function($rootScope, $scope, $q, Api, $routeSegment, $location, $anchorScroll, $timeout, $window, appHistory, Helpers, appCache, Layout, backend, Data) {
        // <Report>
        var reportWindow = function () {
                if (!$rootScope.apd.isLogged) {
                    $rootScope.go('login');
                    return false;
                }
                $scope.view_profile = false;
                Layout.showModal({
                    scope: $scope,
                    position: 'center',
                    include: 'views/profile/report.html',
                    buttons: [{
                            text: $rootScope.l('btn_send'),
                            class: 'btn-primary',
                            action: function () {
                                $scope.report(
                                        Data.getUserId(),
                                        $scope.reportReason,
                                        $scope.reportComment
                                        );
                                $scope.reportReason = Object.keys($scope.reportReasons.option)[0];
                                $scope.reportComment = '';
                                $scope.view_profile = true;
                            }
                        },
                        {
                            text: $rootScope.l('btn_close'),
                            class: 'btn-primary',
                            action: function () {
                                $scope.view_profile = true;
                            }
                        }]
                });
        };
        var getReportReasons = function () {
            if ($rootScope.apd.isLogged) {
                Api.indicator.setSmall();
                Api.query({module: 'spam', method: 'get_reasons'}).then(function (resp) {
                    Api.indicator.setNormal();
                    $scope.reportReasons = resp.reasons;
                    $scope.reportReason = Object.keys($scope.reportReasons.option)[0];
                });
            }
            return [];
        };
        $scope.reportReasons = getReportReasons();
        $scope.reportReason = null;
        $scope.reportComment = '';
        $scope.report = function (objectId, reasonId, comment) {
            var post = {
                'type_gid': 'users_object',
                'object_id': objectId,
                'data[id_reason]': reasonId,
                'data[message]': comment
            };
            Api.query({module: 'spam', method: 'mark_as_spam'}, post).then(function (resp) {
                Layout.addAlert('info', resp.messages);
            }, function (resp) {
                Layout.addAlert('warning', resp.errors);
            });
        };
        // </Report>
        
        
        //<Right button menu>
        $rootScope.rightBtn = {
            icon: 'fa fa-ellipsis-v',
            dropdown: true
        };
        
        $rootScope.reportBtn = {
            text: $rootScope.l('report_abuse'),
            click: reportWindow
        };
        Data.getBlacklistAction();
        //</Right button menu>
        
        // Contact info
	var contactId = $routeSegment.$routeParams.contactId;
	$rootScope.actions = {
		text: $rootScope.l('im'),
		click: function() {
			$rootScope.go('profile/' + contactId);
		}
	};
	$rootScope.leftBtn = {
		class: 'fa fa-arrow-left',
		click: function() {
			appHistory.goBack('im');
		}
	};

	$scope.gotoBottom = function() {
                $timeout(function() {     
                    $window.document.documentElement.scrollTop = $window.document.documentElement.scrollHeight;
                }, 0, false);
	};
	var checkService = function(data) {
		if (0 === data.im_status.im_service_access) {
            if ($rootScope.device == 'iosDevice') {
                Layout.addAlert('warning', $rootScope.l('im_disabled'), true);
                $rootScope.go('/home');
            } else {
                Layout.addAlert('warning', $rootScope.l('im_service_required'), true);
                $rootScope.go('services/view_service/im', false);
                return false;
            }
		}
		return true;
	};

	// TODO: infinite sctoll

	// Get messages
	$scope.getMessages = function() {
		var dfd = $q.defer();
		Api.indicator.setSmall();
		Api.query({module: 'im', method: 'get_messages'}, {id_contact: contactId, count: 10}).then(function(resp) {
			Api.indicator.setNormal();
			$scope.data = resp.data;
			if (!checkService(resp.data)) {
				return false;
			}
			$scope.data.msg = $.map($scope.data.msg, function(value, index) {
				value.text = value.text.replace(/\n/g, '<br>');
				return [value];
			});
			$scope.gotoBottom();
			dfd.resolve();
		}, function(resp) {
			if(resp.status != '403')
			{
				Api.indicator.setNormal();
				Layout.addAlert('warning', $rootScope.l('im_disabled'), true);
				appHistory.goBack();
				dfd.reject();
			}
		});
		return dfd.promise;
	};
	$scope.getMessages();

	if (Helpers.isObjEmpty($scope.contact)) {
		Api.query({module: 'users', method: 'get'}, {id: contactId, formatted: true}).then(function(resp) {
			$scope.contact = resp.data;
			$rootScope.actions.text = $scope.contact.output_name;
		}, function(resp) {
			$scope.contact = false;
			Layout.addAlert('warning', resp.errors, false);
		});
	} else {
		$rootScope.actions.text = $scope.contact.output_name;
	}
	$scope.im = {
		text: ''
	};
	// Send message
	$scope.send = function() {
		
		Api.indicator.setSmall();
		var data = {
			id_contact: contactId, 
			text: $scope.im.text
		};
		Api.query({module: 'im', method: 'post_message'}, data).then(function(resp) {
			Api.indicator.setNormal();
			if (!checkService(resp.data)) {
				return false;
			}
			// TODO: Don't update the entire list
			$scope.getMessages().then(function() {
				$scope.im.text = '';
			});
		}, function() {
			Api.indicator.setNormal();
			Layout.addAlert('warning', $rootScope.l('im_disabled'), true);
			appHistory.goBack();
		});
	};

	// Check new messages
	backend.add({
		name: 'imChat',
		data: {
			module: 'im',
			model: 'Im_contact_list_model',
			method: 'check_new_messages'
		},
		callback: function(resp) {
			//if (resp.count_new > 0) {
				//why?
				//$scope.getMessages();
			//}
		}
	});

});
