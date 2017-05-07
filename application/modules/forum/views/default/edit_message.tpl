{include file="header.tpl"}
<h1>{if $data.id}{l i='admin_header_message_change' gid='forum'}{else}{l i='admin_header_message_add' gid='forum'}{/if}</h1>
<form method="post" action="{$site_url}forum/edit_message/{$subcategory_id}" name="save_form">
	<div class="edit-form n150">
		<div class="r">
			<div class="f">{l i='field_subject' gid='forum'}: </div>
			<div class="v"><input type="text" value="{$data.subject}" name="subject"></div>
		</div>
		<div class="r">
			<div class="f">{l i='field_message' gid='forum'}: </div>
			<div class="v"><textarea id="message" name="message" style="width: 300px; height: 200px;">{$data.message}</textarea></div>
		</div>
		<script>{literal}
			var CKEDITOR_BASEPATH = '{/literal}{$site_url}system/plugins/ckeditor/{literal}';
			loadScripts(["{/literal}{$site_url}system/plugins/ckeditor/ckeditor.js{literal}"],
				function(){
					CKEDITOR.replace('message', {customConfig:'{/literal}{$site_url}system/plugins/ckeditor/config.js{literal}',language: '{/literal}{$_LANG.code}{literal}', toolbar: 
						[
							{ name: 'document', items: [ 'Source' ] },
							{ name: 'actions', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
							{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
							{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'SpecialChar' ] },
							{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
							{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
							{ name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
							{ name: 'colors', items: [ 'TextColor', 'BGColor' ] }
						]
					});
				},
				'',
				{async: false}
			);
		</script>{/literal}
	
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a href="{$site_url}forum/messages/{$subcategory_id}" class="btn-link"><i class="icon-arrow-left icon-big edge hover"></i><i>{l i='btn_cancel' gid='start' type='button'}</i></a>
</form>
<div class="clr"></div>

{include file="footer.tpl"}