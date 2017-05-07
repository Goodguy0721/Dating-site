{include file="header.tpl" load_type='ui'}

{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_forum_menu'}

<div class="actions">
	<ul>
		<li><div class="l"><a href="{$site_url}admin/forum/edit_category/">{l i='link_add_category' gid='forum'}</a></div></li>
		<li><div class="l"><a href="{$site_url}admin/forum/index/">{l i='link_view_mode' gid='forum'}</a></div></li>
		<li><div class="l"><a href="#" onclick="javascript: mlSorter.update_sorting(); return false">{l i='link_save_sorting' gid='forum'}</a></div></li>
	</ul>
	&nbsp;
</div>

<div id="menu_items">
	<ul name="parent_0" class="sort connected" id="clsr0ul">
	{foreach item=item from=$categories}
	<li id="item_{$item.id}">{$item.category}</li>
	{/foreach}
	</ul>
</div>
{js file='admin-multilevel-sorter.js'}
{literal}<script type='text/javascript'>
	var mlSorter;
	$(function(){
		mlSorter = new multilevelSorter({
			siteUrl: '{/literal}{$site_url}{literal}',
			onActionUpdate: false,
			urlSaveSort: 'admin/forum/ajax_category_sort'
		});
	});
</script>{/literal}

{include file="footer.tpl"}
