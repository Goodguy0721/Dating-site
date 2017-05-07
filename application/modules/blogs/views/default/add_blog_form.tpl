<div style="margin-top:15px">
	<form method="post" enctype="multipart/form-data">
		<div class="r">
			<div class="f">{l i='field_title' gid='blogs'}: </div>
			<div class="v"><input type='text' value='{$data.title}' name="title"></div>
		</div>
		<div class="r">
			<div class="f">{l i='field_category' gid='blogs'}:</div>
			<div class="v">
				<select name="category">
					{foreach item=item key=key from=$categories.option}<option value="{$key}"{if $key eq $data.category} selected{/if}>{$item}</option>{/foreach}
				</select>
			</div>
		</div>
		<div class="r">
			<div class="v">
				<input id="is_hidden" type='checkbox' value='1' name="is_hidden" {if $data.is_hidden}checked {/if}/>
				<label for="is_hidden">{l i='field_hidden_blog' gid='blogs'}</label>
			</div>
		</div>
		
		<div class="r">
			<div class="f">{l i='field_short_decription' gid='blogs'}: </div>
			<div class="v">
				<textarea id="description" name="description" style="width: 300px; height: 200px;"></textarea>
			</div>
		</div>
		
		<div class="r">
			<div class="f">{l i='field_tags' gid='blogs'}: </div>
			<div class="v"><input type='text' value='{$data.tags_str}' name="tags"></div>
		</div>
		
		<div class="r">
			<div class="f">&nbsp;</div>
			<div class="v">
				<input type="submit" value="{l i='btn_add' gid='start' type='button'}" name="btn_save">
			</div>
		</div>
	</form>
</div>

<script>{literal}
	var CKEDITOR_BASEPATH = '{/literal}{$site_url}system/plugins/ckeditor/{literal}';
	loadScripts(["{/literal}{$site_url}system/plugins/ckeditor/ckeditor.js{literal}"],
		function(){
			CKEDITOR.replace('description', {customConfig:'{/literal}{$site_url}system/plugins/ckeditor/config.js{literal}',language: '{/literal}{$_LANG.code}{literal}', toolbar: 
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