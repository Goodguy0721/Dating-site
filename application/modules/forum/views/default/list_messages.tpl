{include file="header.tpl"}
	<h1>{$subcategory.subcategory}</h1>
	<a class="fright mr5" href="{$site_url}forum/edit_message/{$subcategory.id}" ><i class="icon-plus icon-big edge hover" title="{l i='link_add_message' gid='forum'}"></i></a>


<div class="table-div wp100">
	<dl>
		<dt class="w150"><h2>{l i='field_author' gid='forum'}</h2></dt>
		<dt><h2>{l i='field_message' gid='forum'}</h2></dt>
		<dt class="w100 center"></dt>
	</dl>
</div>
{foreach item=item key=key from=$messages}
	<div class="table-div wp100">
		<dl>
			<dt class="w150" style="vertical-align:top">{if $item.is_admin eq 1}{l i='admin_name' gid='forum'}{else}<a href="{$site_url}users/view/{$item.user.id}">{$item.user.output_name}</a>{/if}<br/>{$item.date_created|date_format:$page_data.date_format}</dt>
			<dt class="wysiwyg">{$item.subject}<br/>{$item.message}</dt>
			<dt class="w100 center">
				<a class="mr5" href="{$site_url}forum/edit_message/{$subcategory.id}/0/{$item.id}" ><i class="icon-quote-right  icon-big edge hover" title="{l i='link_quote' gid='forum'}"></i></a>
				{if $item.user.id eq $user_session_data.user_id}
				<a class="mr5" href="{$site_url}forum/edit_message/{$subcategory.id}/{$item.id}" ><i class="icon-pencil icon-big edge hover" title="{l i='link_edit_message' gid='forum'}"></i></a>
				<a class="mr5" href="javascript:void(0)" onclick="{literal}javascript: if(!confirm('{/literal}{l i='note_delete_message' gid='forum' type='js'}{literal}')) {return false;}else{document.location='{/literal}{$site_url}{literal}forum/delete_message/{/literal}{$item.id}{literal}'}{/literal}"><i class="icon-trash icon-big edge hover" title="{l i='link_delete_message' gid='forum'}"></i></a>
				{/if}
			</dt>
		</dl>
	</div>
{foreachelse}
	<div class="center">{l i='no_messages' gid='forum'}</div>
{/foreach}
{if $messages}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}


{include file="footer.tpl"}
