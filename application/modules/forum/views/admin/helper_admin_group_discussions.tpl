<h2 class="line">
	{l i='group_discussions' gid='groups'}
</h2>

	{if $group_discussions_data.is_leader eq 1}
	<form action="{$site_url}admin/forum/edit_group_category/{$group_discussions_data.group_id}/{$category.id}" method="post">
		<div class="edit-form n150">
			<div class="row">
				<div class="h">{l i='field_category_name' gid='forum'}: </div>
				<div class="v">
					<input type="text" name="category" value="{$category.category}" style="width:450px">
				</div>
			</div>
			<div class="row">
				<div class="h">{l i='field_category_description' gid='forum'}: </div>
				<div class="v"><textarea name="description" style="height:100px;width:450px">{$category.description}</textarea></div>
			</div>
			<div class="row">
				<div class="btn">
					<div class="l">
						<input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}">
					</div>
				</div>
			</div>
		</div>
	</form>
<div class="clr"></div>
	{/if}
	
<table cellspacing="0" cellpadding="0" class="data" width="100%">
<tr>
	<th class="first">{l i='field_categories' gid='forum'}</th>
	<th>{l i='link_subcategory_count' gid='forum'}</th>
	<th>{l i='link_messages_count' gid='forum'}</th>
</tr>
{foreach item=item key=key from=$categories}
{counter print=false assign=counter}
<tr{if $counter is div by 2} class="zebra"{/if}>
	<td><a href="{$site_url}admin/forum/subcategories/{$item.id}">{$item.category}</a></td>
	<td>{$item.subcategory_count}</td>
	<td>{$item.messages_count}</td>
</tr>
{foreachelse}
<tr><td colspan="3" class="center">{l i='no_categories' gid='forum'}</td></tr>
{/foreach}
</table>