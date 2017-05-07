/*'use strict';*/

angular.module('datingMobile').service('appSettings', ['$rootScope', '$cookieStore', function Settings($rootScope, $cookieStore) {
    var self = this;

    try {
        self.isLS = ('localStorage' in window && window['localStorage'] !== null);
        self.storage = localStorage;
        self.storage.setItem('localStorage', 1);
        self.storage.removeItem('localStorage');
    } catch (e) {
        self.storage = new MemoryStorage('datingMobile');
        self.isLS = true;
    }

    var forceCookiesKeys = ['token'/*'lang', 'gallerySlide'*/];
    self.get = function(key){
        key = key || 'settings';
        var value;
        if (self.isLS && !forceCookiesKeys.has(key)) {
            value = self.storage[key];
        }else{
            value = $cookieStore.get(key);
        }
        if(!value || typeof(value) !== 'string') {
            return {};
        }
        if(value.substring(0, 1) === '='){
            return value.substring(1);
        }else{
            if(key === 'l'){
                value = LZString.decompress(value);
            }
            if(value === ''){
                delete self.storage[key];
                return '';
            }
            return JSON.parse(value);
        }
    };

    self.save = function(value, key){
        key = key || 'settings';
        var settings;
        if(typeof value === 'object'){
            settings = JSON.stringify(value);
            if(key === 'l'){
                settings = LZString.compress(settings);
            }
        }else if(typeof value !== 'undefined'){
            settings = '=' + value.toString();
        }
        if(typeof settings !== 'undefined'){
            if (self.isLS && !forceCookiesKeys.has(key)) {
                self.storage[key] = settings;
            }else{
                $cookieStore.put(key, settings);
            }
        }
    };
}]);
