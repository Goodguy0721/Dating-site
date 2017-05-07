/*'use strict';*/

angular.module('datingMobile').controller('LoginCtrl', function ($rootScope, $scope, $location, $window, $q, Init, Layout, appSettings, Api, appHistory, demoEmail, demoPassword) {

	var getSocialApps = function() {
		var dfd = $q.defer();
		Api.query({module: 'users_connections', method: 'social_apps'}).then(function(resp){
			dfd.resolve(
				Object.keys(resp['social_apps']).map(
					function (key) {
						return resp['social_apps'][key];
					}
				)
			);
		}, function(resp) {
			dfd.reject(resp);
		});
		return dfd.promise;
	};
    
	$scope.socialApps = [];
    
	if ($rootScope.android.isObj()) {
		getSocialApps().then(function(socialApps) {
			$scope.socialApps = socialApps;
		});
	}
	$scope.socialAppsIcons = {
		'facebook': 'facebook',
		'vkontakte': 'vk'
	};

	$rootScope.dependOnLang(function() {
		$rootScope.actions = {
			text: $rootScope.l('login')
		};
	});
	$rootScope.leftBtn = {
		class: 'fa fa-arrow-left',
		click: function(){
			appHistory.goBack('start');
		}
	};

	$scope.form = {
		email: '',
		password: ''
	};

	$scope.restoreForm = {
		email: ''
	};
    
    var demoEvent = $rootScope.$watch('apd.data.is_demo', function(){console.log($rootScope.apd.data)
        if ($rootScope.apd.data.is_demo) {
            $scope.form.email = demoEmail;
            $scope.form.password = demoPassword;
        }
        demoEvent();
    });

	$scope.showRestoreForm = false;

	$scope.toggleRestoreForm = function() {
		$scope.showRestoreForm = !$scope.showRestoreForm;
		if($scope.showRestoreForm) {
			$rootScope.leftBtn.click = function() {
				$scope.toggleRestoreForm();
			};
		} else {
			$rootScope.leftBtn.click = function(){
				appHistory.goBack('start');
			};
		}
	};

	$scope.isSaveDisabled = function() {
		return $scope.form.$invalid;
	};

	$scope.restore = function() {
		Api.query({module: 'users', method: 'restore'}, $scope.restoreForm).then(function(resp){
			Layout.addAlert('info', resp.messages, true);
			$scope.toggleRestoreForm();
		}, function(resp) {
			Layout.addAlert('danger', resp.errors, true);
		});
	};

	$scope.oauthLogin = function (id, gid, link) {
		if ($scope.lock) {
			return;
		}
		$scope.lock = true;
		try {
			if ($rootScope.android.isObj()) {
				$rootScope.android.obj[gid + 'Login']();
			}
		} catch (e) {
			$window.location.href = link + '/' + '?redirect=' +
					$location.absUrl().replace('/#!/login/login', '');
		}
	};

	$scope.login = function() {
		Api.updateToken($scope.form.email, $scope.form.password).then(function(resp){
			Layout.removeAlerts(true);
			Init.setUp(true).then(function() {
				appHistory.goBack('main');
			}, function(resp) {
				console.log(resp);
			});
		}, function(err){
			Layout.addAlert('danger', err, true);
		});
	};

	$scope.logOff = function() {
		Api.query({module: 'users', method: 'logout'}).then(function(resp){
			Api.setToken(resp.data.token);
			if ($rootScope.android.isObj()) {
				$rootScope.android.obj.logOut();
				$rootScope.android.obj.setApiToken(resp.data.token);
			}
			Layout.removeAlerts(true);
			appSettings.save(false, 'userData');
			Init.setUp(true).then(function() {
				appHistory.goBack('start');
			});
		}, function(resp) {
			Layout.addAlert('danger', resp.errors, true);
		});
	};
        
        $scope.showTerms = function() {
		Api.query({module: 'content', method: 'get'}, {gid: 'legal-terms'}).then(function(resp) {
			Layout.showModal({
				text: resp.data.page_data.content,
				buttons: [{
					text: $rootScope.l('btn_close'),
					class: 'btn-primary'
				}]
			});
		});
	};
	
}).controller('ConfirmCtrl', function ($rootScope, $scope, Init, Layout, appSettings, Api, appHistory, $location) {
	
	$rootScope.dependOnLang(function() {
		$rootScope.actions = {
			text: $rootScope.l('confirm')
		};
	});
	$rootScope.leftBtn = {
		class: 'fa fa-arrow-left',
		click: function(){
			appHistory.goBack('login');
		}
	};
	
	$scope.form = {
		confirmation_code: ''
	};

	$scope.confirm = function() {
		Api.query({module: 'users', method: 'confirm', uri: $scope.form.confirmation_code}).then(function(resp){
			Layout.addAlert('info', resp.messages, true);
			
			Api.setToken(resp.data.token);
			if ($rootScope.android.isObj()) {
				$rootScope.android.obj.setApiToken(resp.data.token);
			}
			Init.setUp(true).then(function() {
				$location.path('profile');
			}, function(resp) {
			});

			$location.path('profile');
		}, function(resp) {
			Layout.addAlert('danger', resp.errors, true);
			$location.path('main');
		});
	};

});
