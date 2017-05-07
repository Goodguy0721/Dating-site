/*'use strict';*/

angular.module('datingMobile').factory('Data', function($rootScope, $routeSegment, $window, $timeout, $sce, $q, Api, appHistory, appSettings, Layout) {
	var _self = this;
        
        _self.properties = {
            userId: null,
            ownProfile: false,
        };
        
        //helpers
        _self.getUserId = function() {
            return parseInt($routeSegment.$routeParams.userId) || parseInt($routeSegment.$routeParams.contactId) || parseInt(appSettings.get('userData').id) || false;
        }
        
        _self.isOwnProfile = function() {
            return _self.getUserId() === parseInt(appSettings.get('userData').id);
        }
        
        //blacklist
        _self.blacklistAction = function(action) {
            if ($rootScope.apd.isLogged) {
                Api.indicator.setSmall();
                Api.query({module: 'blacklist', method: action, uri: _self.getUserId()}).then(function (resp) {
                    Layout.addAlert('info', resp.messages);   
                    $rootScope.blacklistAction = _self.getBlacklistAction();
                });   
            }
        }
        
        _self.getBlacklistAction = function() {
            if ($rootScope.apd.isLogged && !_self.isOwnProfile()) {
                Api.indicator.setSmall();
                Api.query({module: 'blacklist', method: 'getBlacklistAction', uri: _self.getUserId()}).then(function (resp) {
                    Api.indicator.setNormal();

                    $rootScope.blacklistBtn = {
                        text: $rootScope.l('action_blacklist_' + resp.action),
                        click: function() {
                            _self.blacklistAction(resp.action);
                        }    
                    };
                });
            }

            return [];
        }
        
        return _self;
});
