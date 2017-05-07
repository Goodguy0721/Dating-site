{include file="header.tpl"}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_blods_menu'}

<div class="actions">
	<!--ul>
		<li><div class="l"><a href="{$site_url}admin/blogs/edit">{l i='link_add_blog' gid='blogs'}</a></div></li>

	</ul-->
	&nbsp;
</div>
<div class="n150 pb10">
	<div class="row">
		<div class="r p5">{l i='field_category' gid='blogs'}: </div>
		<div class="v">
			<select name="content_type" onchange="document.location='{$site_url}admin/blogs/index/'+this.value">
				<option value="all">{l i='all_blogs' gid='blogs'}</option>	
				{foreach item=item key=key from=$categories.option}<option value="{$key}"{if $key eq $category} selected{/if}>{$item}</option>{/foreach}
			</select>
		</div>
	</div>
</div>
<div class="clr"></div>


<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><a href="{$sort_links.title'}"{if $order eq 'title'} class="{$order_direction|lower}"{/if}>{l i='field_title' gid='blogs'}</a></th>
		<th>{l i='field_user' gid='blogs'}</th>
		<th><a href="{$sort_links.date_created'}"{if $order eq 'date_created'} class="{$order_direction|lower}"{/if}>{l i='link_created' gid='blogs'}</a></th>
		<th>{l i='link_type' gid='blogs'}</th>
		<th><a href="{$sort_links.posts_count'}"{if $order eq 'posts_count'} class="{$order_direction|lower}"{/if}>{l i='link_posts_count' gid='blogs'}</a></th>
		<th><a href="{$sort_links.comments_count'}"{if $order eq 'comments_count'} class="{$order_direction|lower}"{/if}>{l i='link_comments_count' gid='blogs'}</a></th>
		<th>{l i='field_category' gid='blogs'}</th>
		<th class="w100">&nbsp;</th>
	</tr>
	{foreach item=item from=$blogs}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first">{$item.title}</td>
			<td><a href="{$site_url}admin/users/edit/personal/{$item.user_id}">{$item.user.output_name}</a></td>
			<td>{$item.date_created}</td>
			<td>{$item.type}</td>
			<td class="center">{if $item.posts_count ne '0'}<a href="{$site_url}admin/blogs/posts/{$item.id}">{$item.posts_count}</a>{else}{$item.posts_count}{/if}</td>
			<td class="center">{$item.comments_count}</td>
			<td>{$item.category_name}</td>
			<td class="icons">
				{if $item.active}
					<a href="{$site_url}admin/blogs/activate_blog/{$item.id}/0"><img src="{$site_root}{$img_folder}icon-full.png" width="16" height="16" border="0" alt="{l i='link_deactivate_blog' gid='blogs'}" title="{l i='link_deactivate_blog' gid='blogs'}"></a>
				{else}
					<a href="{$site_url}admin/blogs/activate_blog/{$item.id}/1"><img src="{$site_root}{$img_folder}icon-empty.png" width="16" height="16" border="0" alt="{l i='link_activate_blog' gid='blogs'}" title="{l i='link_activate_blog' gid='blogs'}"></a>
				{/if}
				<a href="{$site_url}admin/blogs/posts/{$item.id}"><img src="{$site_root}{$img_folder}icon-list.png" width="16" height="16" border="0" alt="{l i='link_view_posts' gid='blogs'}" title="{l i='link_view_posts' gid='blogs'}"></a>
				<a href="{$site_url}admin/blogs/delete_blog/{$item.id}" onclick="javascript: if(!confirm('{l i='note_delete_blog' gid='blogs' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0" alt="{l i='link_delete_blog' gid='blogs'}" title="{l i='link_delete_blog' gid='blogs'}"></a>
				{block name='contact_user_link' module='tickets' id_user=$item.user_id}
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="8" class="center">{l i='no_blogs' gid='blogs'}</td></tr>
	{/foreach}
</table>
{include file="pagination.tpl"}

{literal}
	<script type="text/javascript">
		$(function(){
			$('input#grouping_all').bind('click', function(){
				var checked = $(this).is(':checked');
				if(checked){
					$('input[type=checkbox]').attr('checked', 'checked');
				}else{
					$('input[type=checkbox]').removeAttr('checked');
				}
			});
		});

	</script>
{/literal}

{include file="footer.tpl"}
