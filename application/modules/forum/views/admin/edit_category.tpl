{include file="header.tpl"}
<form method="post" action="" name="save_form">
	<div class="edit-form n150">
		<div class="row header">{if $data.id}{l i='admin_header_category_change' gid='blogs'}{else}{l i='admin_header_category_add' gid='blogs'}{/if}</div>
		<div class="row">
			<div class="h">{l i='field_category_name' gid='forum'}: </div>
			<div class="v"><input type="text" value="{$data.category}" name="category"></div>
		</div>
		<div class="row">
			<div class="h">{l i='field_category_description' gid='forum'}: </div>
			<div class="v"><textarea name="description">{$data.description}</textarea></div>
		</div>
	
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a class="cancel" href="{$site_url}admin/forum/">{l i='btn_cancel' gid='start'}</a>
</form>
<div class="clr"></div>

<script>{literal}
$(function(){
	$("div.row:odd").addClass("zebra");
});
{/literal}</script>

{include file="footer.tpl"}