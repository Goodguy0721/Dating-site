/*'use strict';*/

angular.module('datingMobile').controller('ProfileCtrl', function ($rootScope, $routeSegment, $scope, $q, Api, appHistory, appSettings, Layout, Data) {
    var userId = parseInt($routeSegment.$routeParams.userId) || parseInt(appSettings.get('userData').id) || false;
    if (!userId) {
        appHistory.goBack('search');
        return;
    }

    $rootScope.selectFile = function () {
        if ($rootScope.android.isObj()) {
            $rootScope.android.setCallback('setAvatar', $scope.updateUserData);
            $rootScope.android.obj.setAvatar();
        }
    };

    var ownProfile = userId === parseInt(appSettings.get('userData').id);
    $rootScope.actions = {};
    if(ownProfile) {
        $rootScope.rightBtn = {
            icon: 'fa fa-search',
            href: 'search'
        };
    } else {
        $rootScope.rightBtn = {
            icon: 'fa fa-ellipsis-v',
            dropdown: true
        };
    }

    $rootScope.leftBtn = {
        class: 'fa fa-arrow-left',
        click: function () {
            appHistory.goBack('search');
        }
    };

    $scope.updateUserData = function () {
        Api.query({module: 'users', method: 'get'}, {formatted: true}).then(function (userResp) {
            $scope.user = userResp.data;
        });
    };

    // Для обновления аватара
    $scope.onFileSelect = function (file) {
        Api.query({module: 'users', method: 'save'}, {user_icon: file[0]}, 'user_icon', file[0]).then(function () {
            $scope.updateUserData();
        });
    };

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
                                    $scope.user.id,
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

    var setLayout = function () {
        // Есть ли у пользователя картинки
        var checkUserMedia = function (userId) {
            var dfd = $q.defer();
            Api.query({module: 'media', method: 'get_media_count'}, {user_ids: [userId]}).then(function (resp) {
                if ('undefined' !== typeof resp.data && resp.data[$scope.user.id]) {
                    dfd.resolve(true);
                } else {
                    dfd.resolve(false);
                }
            });
            return dfd.promise;
        };
        $scope.view_profile = true;

        $rootScope.reportBtn = {
            text: $rootScope.l('report_abuse'),
            click: reportWindow
        };

        Data.getBlacklistAction();

        if (ownProfile) {
            checkUserMedia(userId).then(function (hasMedia) {
                var galleryAction;
                if (hasMedia) {
                    galleryAction = function () {
                        $rootScope.go('gallery');
                    };
                } else {
                    galleryAction = function () {
                        Layout.addAlert('info', $rootScope.l('gallery_add_photo'), true);
                        $rootScope.go('gallery/edit');
                    };
                }
                $rootScope.actions.items = [{
                        click: galleryAction,
                        text: $rootScope.l('profile_my_photos')
                    }];
            });
            $scope.bigBtns = [
                {
                    icon: 'fa-comments',
                    text: $rootScope.l('messages'),
                    ngHide: 'imDisabled',
                    click: function () {
                        $rootScope.go('im', true);
                    }
                },
                {
                    icon: 'fa-users ',
                    text: $rootScope.l('friends'),
                    click: function () {
                        $rootScope.go('friends');
                    }
                }
            ];
            $scope.canEdit = true;
        } else {
            $scope.canEdit = false;
            var checkFriendRequest = function () {
                // Проверяем запрос на добавление в друзья
                Api.query({module: 'friendlist', method: 'get_statuses'}, {id_dest_user: userId}).then(function (resp) {
                    if (resp.data.allowed_btns.accept.allow) {
                        // Ответ на запрос
                        var friendResponse = function (action) {
                            if (action) {
                                Api.query({module: 'friendlist', method: action}, {id_dest_user: userId}).then(function (resp) {
                                    if (resp.messages) {
                                        Layout.addAlert('info', resp.messages);
                                    }
                                }, function (resp) {
                                    Layout.addAlert('danger', resp.errors);
                                });
                            }
                        };
                        Layout.setTopMessage({
                            close: function () {
                                Layout.removeTopMessage();
                            },
                            text: $scope.user.fname + ' ' + $rootScope.l('friends_request_sent'),
                            buttons: [
                                {
                                    text: $rootScope.l('friends_btn_accept'),
                                    class: 'btn-primary',
                                    action: function () {
                                        friendResponse('accept');
                                    }
                                },
                                {
                                    text: $rootScope.l('friends_btn_decline'),
                                    class: 'btn-default',
                                    action: function () {
                                        friendResponse('decline');
                                    }
                                }
                            ]
                        });
                    }
                });
            };

            $scope.bigBtns = [{
                    icon: 'fa-camera',
                    text: $rootScope.l('photos'),
                    click: function () {
                        checkUserMedia(userId).then(function (hasMedia) {
                            if (hasMedia) {
                                $rootScope.go('gallery/' + userId);
                            } else {
                                Layout.addAlert('info', $rootScope.l('gallery_no_photos'));
                            }
                        });
                    }
                }];

            if ('true' === appSettings.get('isLogged')) {
                checkFriendRequest();
                $scope.bigBtns.unshift({
                    icon: 'fa-comments',
                    text: $rootScope.l('messages'),
                    ngHide: 'imDisabled',
                    click: function () {
                        $rootScope.go('im/' + userId, true);
                    }
                });
            }
        }
    };

    Api.query({module: 'users', method: 'view'}, {id: userId, lang_id: appSettings.get('lang').id}).then(function (resp) {
        $scope.user = resp.data.user;
        $scope.sections = resp.data.sections;
        $rootScope.actions.text = resp.data.user.fname || resp.data.user.nickname;
        setLayout();
    });

}).controller('ProfileEditCtrl', function ($rootScope, $scope, $routeSegment, $timeout, Api, appSettings, appHistory, Init, Helpers, Layout) {
    $rootScope.actions = {
        text: appSettings.get('userData').fname || appSettings.get('userData').nickname
    };
    $rootScope.leftBtn = {
        class: 'fa fa-arrow-left',
        click: function () {
            appHistory.goBack('profile');
        }
    };

    var updateUserData = function () {
        Api.query({module: 'users', method: 'get'}, {formatted: true}).then(function (userResp) {
            $scope.formData = userResp.data;
        });
    };

    $rootScope.selectFile = function () {
        console.log('select');
        if ($rootScope.android.isObj()) {
            console.log('select');
            $rootScope.android.setCallback('setAvatar', updateUserData);
            $rootScope.android.obj.setAvatar();
        }
    };

    var userId = appSettings.get('userData').id;
    var fillFormCustomData = function (fieldsData) {
        for (var key in fieldsData) {
            $scope.formData[fieldsData[key].field_name] = fieldsData[key].value;
        }
        $scope.fieldsData = fieldsData;
    };

    var fillFormPersonalData = function (fieldsData) {
        var personalFields = [
            'age_max',
            'age_min',
            'birth_date',
            'fname',
            'id_city',
            'id_country',
            'id_region',
            'looking_user_type',
            'nickname',
            'sname',
            'user_type',
            'age_min',
            'age_max',
            'user_logo'
        ];
        for (var i = 0; i < personalFields.length; i++) {
            $scope.formData[personalFields[i]] = fieldsData[personalFields[i]];
        }
        $scope.formData.age_min = parseInt($scope.formData.age_min);
        $scope.formData.age_max = parseInt($scope.formData.age_max);
        if (fieldsData.user_logo) {
            $scope.userLogo = fieldsData.media.user_logo.thumbs.middle;
        }
        $scope.location = {
            id_country: $scope.formData.id_country,
            id_region: $scope.formData.id_region,
            id_city: $scope.formData.id_city
        };
        return $scope.formData;
    };

    $scope.age = appSettings.get('properties').age;
    $scope.userTypes = appSettings.get('properties').userTypes;

    $scope.section = $routeSegment.$routeParams.sectionId;
    $scope.formData = {section: $scope.section};

    $scope.onFileSelect = function (file) {
        $scope.formData.user_icon = false;
        $timeout(function () {
            $scope.formData.user_icon = file[0];
        }, 10);
    };

    $scope.save = function () {
        if ('personal' === $scope.section) {
            $scope.formData.section = $scope.section;
            $scope.formData.id_country = $scope.location.id_country;
            $scope.formData.id_region = $scope.location.id_region;
            $scope.formData.id_city = $scope.location.id_city;
        }
        Api.query({module: 'users', method: 'save'}, $scope.formData, 'user_icon', $scope.formData.user_icon).then(function (resp) {
            if (!Helpers.isObjEmpty(resp.user_session_data.errors)) {
                Layout.addAlert('danger', resp.user_session_data.errors, true);
            } else {
                Init.initSettings();
                appHistory.goBack('profile');
            }
        }, function () {
        });
    };

    var data = {
        id: userId,
        section: $scope.section,
        lang_id: appSettings.get('lang').id
    };

    Api.query({module: 'users', method: 'profile'}, data).then(function (resp) {
        if ('personal' === $scope.section) {
            fillFormPersonalData(resp.data);
        } else {
            fillFormCustomData(resp.data.fields_data);
        }
    });

});
