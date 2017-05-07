{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
<div class="menu-level2">
    <!--    <ul>
            <li><div class="l"><a id="events_edit_main_item" href="{$site_url}admin/events/edit_main/{$event_id}">{l i='menu_edit_main_item' gid='events'}</a></div></li>
            <li><div class="l"><a id="events_edit_participants_item" href="{$site_url}admin/events/edit_participants/{$event_id}">{l i='menu_edit_participants_item' gid='events'}</a></div></li>
            <li class="active"><div class="l"><a id="events_edit_album_item" href="{$site_url}admin/events/edit_album/{$event_id}">{l i='menu_edit_album_item' gid='events'}</a></div></li>
        </ul>-->
</div>

<form id="item_form" onsubmit="return" method="post" enctype="multipart/form-data" name="item_form">
    <div class="edit-form n150">
        <div class="row header"></div>	
        <!--		<div class="row">
                                <div class="h">{if $product.photo}{l i='field_product_photo' gid='store'}{/if}</div>
                                <div class="v">
                                    <div id="events_photo">
        {$images_block}
    </div>
</div>
</div>-->
        <div class="row">
            <div class="h">{l i='field_upload_photos' gid='store'}: </div>
            <div class="v">
                <div>{l i='field_max_photo_size' gid='store'}: {if $photo_config.max_size}{l i='max' gid='start'}{$photo_config.max_size|bytes_format}{/if}</div>
                <div>
                    {l i='field_max_width_height_photo' gid='store'}: {$photo_config.max_width}x{$photo_config.max_height}
                </div>
                <div>{l i='field_photo_file_types' gid='store'}: {$photo_config.file_formats_str}</div>
                <div>{l i='field_product_photo_upload_descr' gid='store'}</div>
                <div>
                    <div id="dnd_upload" class="drag">
                        <div id="dndfiles" class="drag-area">
                            <div class="drag">
                                <p>{l i='drag_photos' gid='media'}</p>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="upload-btn">
                            <span data-role="filebutton">
                                <s>{l i='btn_choose_file' gid='start'}</s>
                                <input type="file" name="multiUpload" id="multiUpload" multiple />
                            </span>
                        </div>
                        &nbsp;<span id="attach-input-error"></span>
                        <div id="attach-input-warning"></div>
                    </div>
                </div>
            </div>
        </div>		
    </div>
    <div class="btn"><div class="l"><input type="button" value="{l i='btn_add' gid='start' type='button'}" name="btn_save" id="btn_mupload"></div></div>
    <a class="cancel" href="{$back_url}">{l i='btn_cancel' gid='start'}</a>
</form>

<script type='text/javascript'>
    {literal}
        $(function () {
            loadScripts(["{/literal}{js file='uploader.js' return='path'}{literal}", "{/literal}{js file='events_media.js' module=events return='path'}{literal}"],
                    function () {
                        var photo_mimes = {/literal}{json_encode data=$photo_config.allowed_mimes}{literal};
                        var event_id = {/literal}{$event_id}{literal};
                        events_photo = new events_media({
                            siteUrl: site_url,
                            idEvent: event_id,
                            photoSize: 'big',
                            galleryContentDiv: 'events_photo',
                            lang_delete_confirm: "{/literal}{l i='delete_confirm' gid='media'}{literal}"
                        });
                        photo_uploader = new uploader({
                            Accept: 'application/json',
                            siteUrl: site_url,
                            uploadUrl: 'admin/events/ajaxSaveMedia/photo/' + event_id,
                            zoneId: 'dndfiles',
                            fileId: 'multiUpload',
                            formId: 'item_form',
                            sendType: 'file',
                            sendId: 'btn_mupload',
                            messageId: 'attach-input-error',
                            warningId: 'attach-input-warning',
                            maxFileSize: '{/literal}{$photo_config.max_size}{literal}',
                            mimeType: photo_mimes,
                            cbOnQueueComplete: function (data) {
                                events_photo.reload();
                            },
                            createThumb: true,
                            thumbWidth: 60,
                            thumbHeight: 60,
                            thumbCrop: true,
                            thumbJpeg: false,
                            thumbBg: 'transparent',
                            fileListInZone: true,
                            filebarHeight: 200,
                            jqueryFormPluginUrl: "{/literal}{js file='jquery.form.min.js' return='path'}{literal}"
                        });
                    },
                    ['photo_uploader', 'events_photo'],
                    {async: false}
            );
        });
    {/literal}
</script>
{include file="footer.tpl"}