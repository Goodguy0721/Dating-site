{include file="header.tpl"}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_forum_menu'}

<div class="actions">
	<ul>
		<li><div class="l"><a href="{$site_url}admin/forum/edit_message/{$category.id}/{$subcategory.id}">{l i='link_add_message' gid='forum'}</a></div></li>
	</ul>
	&nbsp;
</div>

<div>
	<p class="mb5">
		<a href="{$site_url}admin/forum/index/{$category.cat_type}">{l i='title_'+$category.cat_type gid='forum'}</a> -> 
		<a href="{$site_url}admin/forum/subcategories/{$category.id}">{$category.category}</a> -> {$subcategory.subcategory}
	</p>
</div>

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first w100">{l i='field_subject' gid='forum'}</th>
		<th>{l i='field_message' gid='forum'}</th>
		<th class="w100">{l i='field_author' gid='forum'}</th>
		<th class="w100">{l i='field_date' gid='forum'}</th>
		<th class="w50">&nbsp;</th>
	</tr>
	{foreach item=item key=key from=$messages}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first">{$item.subject}</td>
			<td class="center">{$item.message}</td>
			<td class="center">{if $item.is_admin eq 1}{l i='admin_name' gid='forum'}{else}<a href="{$site_url}admin/users/edit/personal/{$item.user.id}">{$item.user.output_name}</a>{/if}</td>
			<td class="center">{$item.date_created|date_format:$page_data.date_format}</td>
			<td class="icons">
				{if $item.is_admin ne 1}
					{if $item.is_banned}
						<a href="{$site_url}admin/forum/banned/{$category.id}/{$subcategory.id}/{$item.id}/1"><img src="{$site_root}{$img_folder}icon-full.png" width="16" height="16" border="0" alt="{l i='link_edit_message' gid='forum'}" title="{l i='link_edit_message' gid='forum'}"></a>
					{else}
						<a href="{$site_url}admin/forum/banned/{$category.id}/{$subcategory.id}/{$item.id}/0"><img src="{$site_root}{$img_folder}icon-empty.png" width="16" height="16" border="0" alt="{l i='link_edit_message' gid='forum'}" title="{l i='link_edit_message' gid='forum'}"></a>
					{/if}
				{/if}
				<a href="{$site_url}admin/forum/edit_message/{$category.id}/{$subcategory.id}/{$item.id}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_message' gid='forum'}" title="{l i='link_edit_message' gid='forum'}"></a>
				<a href="{$site_url}admin/forum/delete_message/{$category.id}/{$subcategory.id}/{$item.id}" onclick="javascript: if(!confirm('{l i='note_delete_message' gid='forum' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_message' gid='forum'}" title="{l i='link_delete_message' gid='forum'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="3" class="center">{l i='no_subcategories' gid='forum'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
