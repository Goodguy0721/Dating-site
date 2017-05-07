{include file="header.tpl"}
{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_events_menu'}
<form method="post" action="{$data.action}" name="save_form" enctype="multipart/form-data">
    <div class="edit-form n200">
        <div class="row header">{l i='admin_header_settings' gid='events'}</div>		
		<div class="row">
            <div class="h">{l i='field_is_active' gid='events'}:</div>
            <div class="v">
				<input type="checkbox" name="is_active" value="1" {if $data.is_active}checked{/if}>
			</div>
        </div>
    </div>
    <div class="btn">
        <div class="l">
            <input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}">
        </div>
    </div>
</form>
<div class="clr"></div>
{block name='lang_inline_editor' module='start' textarea='1'}
{include file="footer.tpl"}