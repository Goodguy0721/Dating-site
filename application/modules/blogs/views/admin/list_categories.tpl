{include file="header.tpl"}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_blods_menu'}

<div class="actions">
	<ul>
		<li><div class="l"><a href="{$site_url}admin/blogs/edit_category/">{l i='link_add_category' gid='blogs'}</a></div></li>
	</ul>
	&nbsp;
</div>

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"></th>
		<th>{l i='field_category' gid='blogs'}</th>
		<th>{l i='link_blogs_count' gid='blogs'}</th>
		<th class="w150">&nbsp;</th>
	</tr>
	{foreach item=item key=key from=$categories}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first">{$item.gid}</td>
			<td>{$item.name}</td>
			<td class="center">{if $item.blogs_count ne '0'}<a href="{$site_url}admin/blogs/index/{$item.gid}">{$item.blogs_count}</a>{else}{$item.blogs_count}{/if}</td>
			<td class="icons">
				<a href="{$site_url}admin/blogs/edit_category/{$item.gid}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_categories' gid='blogs'}" title="{l i='link_edit_categories' gid='blogs'}"></a>
				<a href="{$site_url}admin/blogs/delete_category/{$item.gid}" onclick="javascript: if(!confirm('{l i='note_delete_category' gid='blogs' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_category' gid='blogs'}" title="{l i='link_delete_category' gid='blogs'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="4" class="center">{l i='no_categoties' gid='blogs'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
