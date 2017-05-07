{include file="header.tpl"}


<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><a href="{$sort_links.title'}"{if $order eq 'title'} class="{$order_direction|lower}"{/if}>{l i='field_title' gid='blogs'}</a></th>
		<th>{l i='field_user' gid='blogs'}</th>
		<th><a href="{$sort_links.date_created'}"{if $order eq 'date_created'} class="{$order_direction|lower}"{/if}>{l i='link_created' gid='blogs'}</a></th>
		<th>{l i='field_description' gid='blogs'}</th>
		<th class="w100">&nbsp;</th>
	</tr>
	{foreach item=item from=$blog_posts}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first">{$item.title}</td>
			<td><a href="{$site_url}admin/users/edit/personal/{$blog_data.user_id}">{$blog_data.user.output_name}</a></td>
			<td>{$item.date_created}</td>
			<td>{$item.body}</td>
			<td class="icons">
				<a href="{$site_url}admin/blogs/comments/{$item.id}"><img src="{$site_root}{$img_folder}icon-list.png" width="16" height="16" border="0" alt="{l i='link_view_comments' gid='blogs'}" title="{l i='link_view_comments' gid='blogs'}"></a>
				<a href="{$site_url}admin/blogs/edit_post/{$item.id}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_post' gid='blogs'}" title="{l i='link_edit_post' gid='blogs'}"></a>
				<a href="{$site_url}admin/blogs/delete_post/{$item.id}" onclick="javascript: if(!confirm('{l i='note_delete_post' gid='blogs' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_post' gid='blogs'}" title="{l i='link_delete_post' gid='blogs'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="5" class="center">{l i='no_posts' gid='blogs'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
