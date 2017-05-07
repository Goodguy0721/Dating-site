{include file="header.tpl"}
<form method="post" action="" name="save_form">
	<div class="edit-form n150">
		<div class="row header">{if $data.id}{l i='admin_header_subcategory_change' gid='forum'}{else}{l i='admin_header_subcategory_add' gid='forum'}{/if}</div>
		<div class="row">
			<div class="h">{l i='field_subcategory_name' gid='forum'}: </div>
			<div class="v"><input type="text" value="{$data.subcategory}" name="subcategory"></div>
		</div>
		{if !$data.id}
		<div class="row">
			<div class="h">{l i='field_subject' gid='forum'}: </div>
			<div class="v"><input type="text" value="{$data.subject}" name="subject"></div>
		</div>
		<div class="row">
			<div class="h">{l i='field_message' gid='forum'}: </div>
			<div class="v">{$content_fck}</div>
		</div>
		{/if}
	
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a class="cancel" href="{$site_url}admin/forum/subcategories/{$category_id}">{l i='btn_cancel' gid='start'}</a>
</form>
<div class="clr"></div>

<script>{literal}
$(function(){
	$("div.row:odd").addClass("zebra");
});
{/literal}</script>

{include file="footer.tpl"}