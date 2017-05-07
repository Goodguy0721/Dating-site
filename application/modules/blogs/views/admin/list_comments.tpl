{include file="header.tpl"}


<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><a href="{$sort_links.title'}"{if $order eq 'title'} class="{$order_direction|lower}"{/if}>{l i='field_title' gid='blogs'}</a></th>
		<th>{l i='field_user' gid='blogs'}</th>
		<th><a href="{$sort_links.date_created'}"{if $order eq 'date_created'} class="{$order_direction|lower}"{/if}>{l i='link_created' gid='blogs'}</a></th>
		<th>{l i='field_comment' gid='blogs'}</th>
		<th class="w100">&nbsp;</th>
	</tr>
	{foreach item=item from=$comments}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first">{$item.title}</td>
			<td><a href="{$site_url}admin/users/edit/personal/{$item.user_id}">{$item.user.output_name}</a></td>
			<td>{$item.date_created}</td>
			<td>{$item.body}</td>
			<td class="icons">
				<a href="{$site_url}admin/blogs/edit_comment/{$item.id}"><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" border="0" alt="{l i='link_edit_comment' gid='blogs'}" title="{l i='link_edit_comment' gid='blogs'}"></a>
				<a href="{$site_url}admin/blogs/delete_comment/{$item.id}" onclick="javascript: if(!confirm('{l i='note_delete_comment' gid='blogs' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_comment' gid='blogs'}" title="{l i='link_delete_comment' gid='blogs'}"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="5" class="center">{l i='no_comments' gid='blogs'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{include file="footer.tpl"}
