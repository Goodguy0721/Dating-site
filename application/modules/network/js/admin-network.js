"use strict";
function adminNetwork(optionArr) {

	var _self = this;

	this.properties = {
		accessUrl: 'admin/network/get_access',
		btnGetAccess: {},
		btnHideHelp: {},
		btnShowHelp: {},
		fast: {},
		frmNetwork: {},
		hasAccess: false,
		helpBlock: {},
		domainField: {},
		keyField: {},
		interval: 1000,
		isRegistered: true,
		meetsReqs: false,
		parent: {},
		prevFast: '',
		prevSlow: '',
		showHelp: true,
		showHelpBlock: {},
		showHelpInput: {},
		showLog: true,
		siteUrl: '',
		slow: {},
		status: false,
		statusUrl: 'admin/network/ajax_get_status'
	};

	var showElements = function (ids) {
		for (var id in ids) {
			$('#' + ids[id]).show();
		}
	};
	var hideElements = function (ids) {
		for (var id in ids) {
			$('#' + ids[id]).hide();
		}
	};

	var ajax = function (url) {
		return $.ajax({
			dataType: 'json',
			url: _self.properties.siteUrl + url,
			context: document.body
		});
	};

	var getAccess = function () {
		return ajax(_self.properties.accessUrl);
	};

	var getStatus = function () {
		return ajax(_self.properties.statusUrl);
	};

	var setObjects = function () {
		_self.properties.parent = $('.network-content');
		_self.properties.fast = $('#fast', _self.properties.parent);
		_self.properties.slow = $('#slow', _self.properties.parent);
		_self.properties.helpBlock = $('#help-block');
		_self.properties.btnHideHelp = $('#btn-hide-help', _self.properties.helpBlock);
		_self.properties.showHelpBlock = $('#show-help-block');
		_self.properties.btnShowHelp = $('#btn-show-help', _self.properties.showHelpBlock);
		_self.properties.btnGetAccess = $('#btn-get-access');
		_self.properties.frmNetwork = $('#network-form');
		_self.properties.domainField = $('#network-domain');
		_self.properties.keyField = $('#network-key');
		_self.properties.showHelpInput = $('[name="show_help"]', _self.properties.frmNetwork);
	};

	var processStatus = function (status) {
		if (_self.properties.prevSlow !== status.slow.log) {
			_self.properties.prevSlow = status.slow.log;
			_self.properties.slow.html(status.slow.log);
		}
		if (_self.properties.prevFast !== status.fast.log) {
			_self.properties.prevFast = status.fast.log;
			_self.properties.fast.text(status.fast.log);
		}
	};

	var startBackend = function () {
		setInterval(function () {
			getStatus().done(function (result) {
				processStatus(result);
			});
		}, _self.properties.interval);
	};

	this.Init = function (options) {
		_self.properties = $.extend(_self.properties, options);
		setObjects();
		_self.uninit();
		_self.bindEvents();
		if (_self.properties.showLog) {
			startBackend();
		}
		if (_self.properties.showHelp) {
			showHelp();
		} else {
			hideHelp();
		}
		_self.initStep[getStep()]();
	};

	this.uninit = function () {
		_self.properties.frmNetwork.off('submit');
	};

	this.formIsValid = function () {
		return true;
	};

	this.bindEvents = function () {
		_self.uninit();
		_self.properties.frmNetwork.on('submit', function () {
			return _self.formIsValid();
		});
		_self.properties.btnHideHelp.on('click', function () {
			hideHelp();
		});
		_self.properties.btnShowHelp.on('click', function () {
			showHelp();
		});
		_self.properties.btnGetAccess.on('click', function () {
			getAccess().done(function (result) {
				if ('object' !== typeof result.access
						|| 'string' !== typeof result.access.domain
						|| 'string' !== typeof result.access.key) {
					console.error('wrong_access');
					return false;
				} else {
					if (result.access.error.length) {
						error_object.show_error_block(result.access.error, 'error');
					}
					// To step 3
					_self.properties.domainField.val(result.access.domain);
					_self.properties.keyField.val(result.access.key);
					_self.properties.hasAccess = result.access.is_correct;
					_self.initStep[getStep()]();
				}
			});
		});
	};

	var showHelp = function () {
		_self.properties.showHelpInput.val(1);
		_self.properties.showHelpBlock.hide();
		_self.properties.helpBlock.show();
	};

	var hideHelp = function () {
		_self.properties.showHelpInput.val(0);
		_self.properties.showHelpBlock.show();
		_self.properties.helpBlock.hide();
	};

	var getStep = function () {
		var step;
		if (_self.properties.isRegistered) {
			step = 4;
		} else if (!_self.properties.meetsReqs) {
			step = 1;
		} else if (!_self.properties.hasAccess) {
			step = 2;
		} else if (!_self.properties.isRegistered) {
			step = 3;
		} else {
			step = 4;
		}
		return step;
	};

	this.initStep = {
		1: function () {
			showElements(['help-section', 'requirements-section']);
			hideElements(['settings-header', 'access-section', 'settings-section',
				'save-section', 'connection-status-section']);
		},
		2: function () {
			showElements(['network-form', 'access-section',
				'btn-access-section']);
			hideElements(['access-fields-section', 'settings-header',
				'settings-section', 'save-section', 'connection-status-section']);
		},
		3: function () {
			showElements(['network-form', 'settings-section',
				'save-section']);
			hideElements(['upload-photos-section', 'access-section',
				'settings-header', 'connection-status-section',
				'requirements-section']);
		},
		4: function () {
			showElements(['help-section', 'requirements-section', 'network-form',
				'settings-header', 'settings-section', 'save-section', 'log-section',
				'connection-status-section', 'requirements-section', 'upload-photos-section']);
			hideElements(['btn-access-section']);
		}
	};

	_self.Init(optionArr);
}