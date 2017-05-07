/*'use strict';*/

angular.module('datingMobile')
	.factory('Init', function($rootScope, $q, $locale, $document, appSettings, Helpers, Api, backend, appHistory, Layout, iOS) {
		var _self = this;
		var _now = new Date().getTime();
		_self.initMenu = function() {
			var menuItems;
			if ($rootScope.apd.isLogged) {
				var appUrl = $rootScope.getAppUrl();
				menuItems = [
					/*0*/{href: 'main', icon: 'fa-th-large', text: $rootScope.l('page_home')},
					/*1*/{href: 'search', icon: 'fa-search', text: $rootScope.l('page_search')},
					/*2*/{href: 'profile', icon: 'fa-user', text: $rootScope.l('page_my_profile')},
					/*3*/{href: 'im', icon: 'fa-comments', text: $rootScope.l('page_im'), indicator: 'new_messages'},
					/*4*/{href: 'friends', icon: 'fa-users', text: $rootScope.l('page_friends'), indicator: 'new_friends'},
					/*5*/{href: 'services/my', icon: 'fa-file-text-o', text: $rootScope.l('page_account')},
					/*6*/{href: 'services', icon: 'fa-file-text-o', text: $rootScope.l('page_services')},
					/*7*/appUrl ? {click: function() {
							$rootScope.go(appUrl);
						}, icon: 'fa-external-link', text: $rootScope.l('btn_get_app')} : null,
					/*8*/{click: function() {
							$rootScope.goToMainSite();
						}, icon: 'fa-external-link', text: $rootScope.l('btn_back_to_main_site')},
					/*9*/{href: 'settings', icon: 'fa-cog', text: $rootScope.l('page_settings')}
				];
				switch($rootScope.device){
					case 'iosDevice':
						menuItems.splice(5,4);
					break;
					case 'iosBrowser':
					break;
					case 'androidDevice':
						delete menuItems[7];
					break;
					case 'androidBrowser':
					break;
					default:
						delete menuItems[7];
					break;
				}
			} else {
				menuItems = [
					{href: 'start', icon: 'fa-th-large', text: $rootScope.l('page_index')},
					{href: 'search', icon: 'fa-search', text: $rootScope.l('page_search')},
					{href: 'settings', icon: 'fa-cog', text: $rootScope.l('page_settings')}
				];
			}
			$rootScope.mainMenu = {items:menuItems};
			// TODO: Перенести в Layout
			Layout.setActiveMenuItem();
			$rootScope.$on('$routeChangeStart', function() {
				// Сбрасываем верхнюю панель
				$rootScope.leftBtn = false;
				$rootScope.rightBtn = false;

				// TODO: Перенести в Layout
				Layout.enableSideMenu();
				Layout.hideSideMenu();
				Layout.setActiveMenuItem();
				Layout.removeAlerts();
				Layout.removeTopMessage();
				Layout.hideModal();
			});
		};

		var initWatchers = function() {
			// Чтобы если обновляются ланги, все об этом знали
			$rootScope.$watch('apd.l', function(newVal, oldVal) {
				if(newVal !== oldVal) {
					$rootScope.$broadcast('langsUpdated');
				};
			});

			$rootScope.$watch('apd.lang', function () {
				if ('undefined' !== typeof $rootScope.apd.lang
						&& $rootScope.apd.lang
						&& !angular.isUndefined(locales[$rootScope.apd.lang.code])) {
					for (var i in locales[$rootScope.apd.lang.code]) {
						$locale[i] = locales[$rootScope.apd.lang.code][i];
					}
				}
			});

			$rootScope.$watch('apd.data.cssUrl', function(newVal, oldVal){
				if(newVal !== oldVal && typeof($rootScope.apd.data.cssUrl) === 'string') {
					angular.element($document[0].getElementsByTagName('head')[0])
							.append('<link id="theme" href="' + $rootScope.apd.data.cssUrl + '" rel="stylesheet">');
				}
			});
		};

		_self.initBackend = function() {
			// Обновление индикаторов
			backend.reset();
			if ($rootScope.apd.isLogged) {
				backend.add({
					name: 'indicators',
					data: {
						module: 'mobile',
						model: 'mobile_model',
						method: 'get_indicators',
						indicators: [
							'new_messages',
							'new_friends'
						]
					},
					callback: function(resp) {
						$rootScope.indicators = resp;
					}
				});
			}
			backend.start();
		};

		_self.initSettings = function(force) {
			force = force || false;
			var _settings = {
				l: appSettings.get('l'),
				lang: appSettings.get('lang'),
				langs: appSettings.get('langs'),
				data: appSettings.get('data'),
                properties: appSettings.get('properties'),
                not_editable_fields: appSettings.get('not_editable_fields'),
                modules: appSettings.get('modules'),
				userData: appSettings.get('userData'),
				isLogged: 'false' !== appSettings.get('isLogged'),
				time: parseInt(appSettings.get('time'))
			};
			$rootScope.apd = _settings;

			initWatchers();

			var _deferred = $q.defer();

			var _updateSettings = force || Helpers.isObjEmpty(_settings.l) ||
				Helpers.isObjEmpty(_settings.data) ||
				Helpers.isObjEmpty(_settings.userData) ||
				_settings.time.isNaN ||
				(_settings.time + 3 * 1000 < _now) || true;
			if (_updateSettings) {
				var langId;
				if (appSettings.get('lang')) {
					langId = appSettings.get('lang').id;
				}
				Api.query({module: 'mobile', method: 'init', uri: langId}, {is_app: true}).then(function(resp) { 
					angular.extend(_settings, resp.data);
					_settings.isLogged = false !== _settings.userData;
					appSettings.save(_settings.l, 'l');
					appSettings.save(_settings.properties, 'properties');
                    appSettings.save(_settings.not_editable_fields, 'not_editable_fields');
					_settings.data.cssUrl += 'mobile-ltr.css';
					appSettings.save(_settings.data, 'data');
             
                    
           
					appSettings.save(_settings.userData, 'userData');
					appSettings.save(_settings.languages, 'langs');
					appSettings.save(_settings.modules, 'modules');
					// Make sure we have the language
					if('object' !== typeof _settings.language) {
						for(var i in _settings.languages) {
							if(1 === parseInt(_settings.languages[i].is_default)) {
								_settings.language = _settings.languages[i];
								break;
							}
						}
					}
					appSettings.save(_settings.language, 'lang');
					appSettings.save(_settings.isLogged, 'isLogged');
					appSettings.save(_now, 'time');
					if ($rootScope.android.isObj()) {
                        if (_settings.isLogged) {
                            $rootScope.android.obj.saveUser(JSON.stringify(_settings.userData));
                        }
						$rootScope.android.obj.saveSettings(JSON.stringify(_settings));
					}
					$rootScope.apd = _settings;
					_deferred.resolve(_settings);
				}, function(resp) {
					console.log('initSettings error:' + resp);
				});
			} else {              
				$rootScope.apd = _settings;
				_deferred.resolve(_settings);
			}
			return _deferred.promise;
		};

		_self.checkIm = function() {
			if ($rootScope.apd.isLogged) {
				$rootScope.imDisabled = false;
				Api.query({module: 'im', method: 'get_im_status'}).then(function(resp) {
					if(undefined === resp.data) {
						console.error('checkIm error');
						return false;
					}
					$rootScope.imDisabled = !!!resp.data.im_on;
				}, function() {
					$rootScope.imDisabled = true;
				});
			}
		};

		_self.setUp = function(force) {
			var deferred = $q.defer();
			_self.checkDevice();
			_self.initSettings(force).then(function(settings) {
				_self.checkIm();
				_self.initBackend();
				_self.initMenu();
				appHistory.init();
				deferred.resolve(settings);
			});
			return deferred.promise;
		};

		_self.checkDevice = function(){
			if(navigator.userAgent.match(new RegExp('iphone|ipad', 'i'))){
				$rootScope.device = 'iosBrowser';
                $rootScope.canRegister = true;
            }else if(navigator.userAgent.match(new RegExp('android', 'i'))) {
				$rootScope.device = 'androidBrowser';
				$rootScope.canRegister = true;
			} else {
				$rootScope.device = 'desktop';
				$rootScope.canRegister = true;
			}
		};

		$rootScope.reInit = function(token) {
			if('undefined' !== typeof token) {
				Api.setToken(token);
			}
			_self.setUp(true).then(function() {
				$rootScope.go('start');
			});
			return {};
		};
		
		$rootScope.execute = function(callback) {
			callback();
		};

		return _self;
	});
