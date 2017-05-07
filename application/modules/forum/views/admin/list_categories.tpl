{include file="header.tpl"}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_forum_menu'}

<div class="actions">
	{if $type eq 'public'}
		<ul>
			<li><div class="l"><a href="{$site_url}admin/forum/edit_category/">{l i='link_add_category' gid='forum'}</a></div></li>
			<li><div class="l"><a href="{$site_url}admin/forum/sorting/">{l i='link_sorting_mode' gid='forum'}</a></div></li>
		</ul>
	{/if}
	&nbsp;
</div>

<div>
	<p class="mb5">{l i='title_'+$type gid='forum'}</p>
	<p class="mb5">{l i='category_counts' gid='forum'}: {$categories_count}</p>
</div>

<div class="menu-level3">
	<ul>
		<li class="{if $type eq 'public'}active{/if}"><a href="{$site_url}admin/forum/index/public">{l i='filter_public' gid='forum'}</a></li>
		<li class="{if $type eq 'club'}active{/if}"><a href="{$site_url}admin/forum/index/club">{l i='filter_club' gid='forum'}</a></li>
	&nbsp;
</div>

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first">{l i='field_categories' gid='forum'}</th>
		<th>{l i='field_category_description' gid='forum'}</th>
		<th>{l i='link_subcategory_count' gid='forum'}</th>
		<th>{l i='link_messages_count' gid='forum'}</th>
		<th class="w150">&nbsp;</th>
	</tr>
	{foreach item=item key=key from=$categories}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first"><a href="{$site_url}admin/forum/subcategories/{$item.id}">{$item.category}</a></td>
			<td>{$item.description}</td>
			<td class="center">{$item.subcategory_count}</td>
			<td class="center">{$item.messages_count}</td>
			<td class="icons">
				<a href="{$site_url}admin/forum/edit_category/{$item.id}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_category' gid='forum'}" title="{l i='link_edit_category' gid='forum'}"></a>
				<a href="{$site_url}admin/forum/delete_category/{$item.id}" onclick="javascript: if(!confirm('{l i='note_delete_category' gid='forum' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_category' gid='forum'}" title="{l i='link_delete_category' gid='forum'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="5" class="center">{l i='no_categories' gid='forum'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
