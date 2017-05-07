{include file="header.tpl"}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_forum_menu'}

<div class="actions">
	<ul>
		<li><div class="l"><a href="{$site_url}admin/forum/edit_subcategory/{$category.id}">{l i='link_add_subcategory' gid='forum'}</a></div></li>
	</ul>
	&nbsp;
</div>

<div>
	<p class="mb5"><a href="{$site_url}admin/forum/index/{$category.cat_type}">{l i='title_'+$category.cat_type gid='forum'}</a> -> {$category.category}</p>
</div>

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first">{l i='field_subcategories' gid='forum'}</th>
		<th class="w100">{l i='link_messages_count' gid='forum'}</th>
		<th class="w100">&nbsp;</th>
	</tr>
	{foreach item=item key=key from=$subcategories}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first"><a href="{$site_url}admin/forum/messages/{$item.id}">{$item.subcategory}</a></td>
			<td class="center">{$item.messages_count}</td>
			<td class="icons">
				<a href="{$site_url}admin/forum/edit_subcategory/{$category.id}/{$item.id}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_subcategory' gid='forum'}" title="{l i='link_edit_subcategory' gid='forum'}"></a>
				<a href="{$site_url}admin/forum/delete_subcategory/{$item.id}/{$category.id}" onclick="javascript: if(!confirm('{l i='note_delete_subcategory' gid='forum' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_subcategory' gid='forum'}" title="{l i='link_delete_subcategory' gid='forum'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="3" class="center">{l i='no_subcategories' gid='forum'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
