<h2 class="line top bottom linked">
	{l i='group_discussions' gid='groups'}
	{if $group_discussions_data.is_leader eq 1}<a class="fright mr5" href="javascript:void(0)" onclick="ForumCategoryForm('{$group_discussions_data.group_id}');"><i class="icon-plus icon-big edge hover" title="{l i='link_add_category' gid='forum'}"></i></a>{/if}
</h2>
<div class="table-div wp100">
	<dl>
		<dt class="w100"><h2>{l i='field_categories' gid='forum'}</h2></dt>
		<dt class="w50 center">{l i='link_subcategory_count' gid='forum'}</dt>
		<dt class="w50 center">{l i='link_messages_count' gid='forum'}</dt>
	</dl>
</div>
{foreach item=item key=key from=$categories}
	<div class="table-div wp100">
		<dl style="height:50px">
			<dt class="w100"><a href="{$site_url}forum/topics/{$item.id}">{$item.category}</a>
				{if $group_discussions_data.is_leader eq '1'}
					<a class="fright" href="javascript:void(0)" onclick="javascript: {literal}if(!confirm('{/literal}{l i='note_delete_category' gid='forum' type='js'}{literal}')) {return false;}else{document.location='{/literal}{$site_url}forum/delete_category/{$item.id}/{$group_discussions_data.group_id}{literal}'}{/literal}"><i class="icon-trash icon-big edge hover" title="{l i='link_delete_category' gid='forum'}"></i></a>
					<a class="fright mr5" href="javascript:void(0)" onclick="ForumCategoryForm('{$group_discussions_data.group_id}', '{$item.id}');"><i class="icon-pencil icon-big edge hover" title="{l i='link_edit_category' gid='forum'}"></i></a>
				{/if}
			</dt>
			<dt class="w50 center">{$item.subcategory_count}</dt>
			<dt class="w50 center">{$item.messages_count}</dt>
		</dl>
	</div>
{foreachelse}
	<div class="center">{l i='no_categories' gid='forum'}</div>
{/foreach}

{if $group_discussions_data.is_leader eq 1}
{literal}<script>
	$(function(){
		forum_popup = new loadingContent({
			loadBlockWidth: '500px',
			closeBtnClass: 'w'
		}).update_css_styles({'z-index': 2000}).update_css_styles({'z-index': 2000}, 'bg');
	});
	function ForumCategoryForm(group_id, category_id){
		if (!group_id) return;
		category_id = category_id || '';
		$.ajax({
			url: site_url + 'forum/ajax_category_form/'+group_id+'/'+category_id,
			cache: false,
			success: function(data){
				forum_popup.show_load_block(data);
			}
		});
	}
</script>{/literal}
{/if}