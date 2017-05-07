{include file="header.tpl"}
{helper func_name=get_admin_level1_menu helper_name=menu func_param='admin_questions_menu'}
<form method="post" action="{$data.action}" name="save_form" enctype="multipart/form-data">
	<div class="edit-form n250">
		<div class="row header">{l i='admin_header_settings_edit' gid='questions'}</div>
		<div class="row">
			<div class="h">{l i='admin_settings_use_module' gid='questions'}:</div>
			<div class="v">				
				<input type="hidden" name="is_active"  value="0">
				<input type="checkbox" name="is_active" value="1" {if $data.is_active eq '1'}checked{/if}>
			</div>
		</div>
		<div class="row zebra">
			<div class="h">{l i='admin_settings_allow_own_question' gid='questions'}:</div>
			<div class="v">
				<input type="hidden" name="allow_own_question"  value="0">
				<input type="checkbox" name="allow_own_question" value="1" {if $data.allow_own_question eq '1'}checked{/if}>
				
			</div>
		</div>
		<div class="row">
			<div class="h">{l i='admin_settings_choose_action_communication' gid='questions'}:</div>
			<div class="v">			
				<select name="action_for_communication">
					{foreach item='item' key='key' from=$data.action_for_communication}						
						<option value="{$key}" {if $item.selected}selected{/if}>{$item.name}</option>
					{/foreach}
				</select>			
			</div>
		</div>
		<div class="row zebra">
			<div class="h">{l i='admin_settings_action_description' gid='questions'}:</div>
			<div class="v">
				<div>
				{foreach item='lang_item' key='lang_id' from=$data.action_description}
					{if $lang_id eq $current_lang_id}
						<textarea name="action_description[{$lang_id}]" rows="5" cols="80" class="long" lang-editor="value" lang-editor-type="data-action_description" lang-editor-lid="{$lang_id}">{$lang_item|escape}</textarea>
					{else}
						<input type="hidden" name="action_description[{$lang_id}]" value="{$lang_item|escape}" lang-editor="value" lang-editor-type="data-action_description" lang-editor-lid="{$lang_id}" />
					{/if}
				{/foreach}
				<a href="#" lang-editor="button" lang-editor-type="data-action_description"><img src="{$site_root}{$img_folder}icon-translate.png" width="16" height="16"></a>
				</div>
			</div>
		</div>
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a class="cancel" href="{$site_url}admin/start/menu/add_ons_items">{l i='btn_cancel' gid='start'}</a>
</form>
<script>{literal}
$(function(){
	var status = $('#play_local_used').prop('checked');
	if(status){
		$('#play_local_area').show();
	}else{
		$('#play_local_area').hide();
	}
	$('#play_local_used').click(function(){
		$('#play_local_area').toggle();
	});
});
{/literal}</script>

{block name='lang_inline_editor' module='start' textarea='1'}

{include file="footer.tpl"}
