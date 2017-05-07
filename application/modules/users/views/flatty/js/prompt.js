function prompt(optionArr) {
    this.properties = {
        site_url: site_url,
        load_step_1: 'users/prompt/1',
        load_step_2: 'users/prompt/2',
        window_obj: new loadingContent({
            loadBlockWidth: '800px',
            closeBtnClass: 'w',
            loadBlockTopType: 'top',
            loadBlockTopPoint: 20,
            blockBody: true,
            showAfterImagesLoad: false
        })
    };

    var _self = this;

    this.Init = function (options) {
        _self.properties = $.extend(_self.properties, options);

        $(document).on('click', '#photo-upload', function (e) {
          e.preventDefault();
          $('#user_logo').trigger('click');
        });

        $(document).on('click', '#photo-skip', function (e) {
          e.preventDefault();
          //_self.properties.window_obj.hide_load_block();
          locationHref(site_url + 'users/profile');
        });

        _self.step1();

        return this;
    };

    this.uninit = function () {
        $(document).off('click', '#photo-upload,#photo-skip');
        return this;
    };

    this.step1 = function () {
      $.ajax({
          url: _self.properties.site_url + _self.properties.load_step_1,
          type: 'GET',
          dataType: 'html',
          cache: false,
          success: function (resp) {
              if (!resp) {
                return;
              }
              _self.properties.window_obj.show_load_block(resp);
              new uploader({
                siteUrl: _self.properties.site_url,
                Accept: 'application/json',
                uploadUrl: 'users/ajax_save_avatar/' + _self.properties.user_id,
                zoneId: '',
                fileId: 'user_logo',
                formId: 'item_form',
                sendType: 'auto',
                sendId: 'btn_upload',
                messageId: 'attach-input-error',
                warningId: 'attach-input-warning',
                maxFileSize: '1024000000',
                mimeType: ['image/png','image/jpeg','image/jpg'],
                lang: {errors: _self.properties.errors},
                cbOnQueueComplete: function (data) {
                  _self.step2();
                },
                cbOnError: function (data) {
                  alert('error');
                },
                fileListInZone: true,
                filebarHeight: 200,
              });
          }
      });
    };

    this.step2 = function () {
      $.ajax({
          url: _self.properties.site_url + _self.properties.load_step_2,
          type: 'GET',
          dataType: 'html',
          cache: false,
          success: function (resp) {
            _self.properties.window_obj.hide_load_block();
            $('body').append(resp);
          }
      });
    };

    _self.Init(optionArr);

    return this;
}
