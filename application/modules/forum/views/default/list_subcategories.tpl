{include file="header.tpl"}
	<h1>{$category.category}</h1>
	<a class="fright mr5" href="{$site_url}forum/edit_topic/{$category.id}" ><i class="icon-plus icon-big edge hover" title="{l i='link_add_subcategory' gid='forum'}"></i></a>

{*if $categories}
	<div class="sorter short-line" id="sorter_block">
		<div class="fright">{pagination data=$page_data type='cute'}</div>
	</div>
{/if*}
<div class="table-div wp100">
	<dl>
		<dt class="w100"><h2>{l i='field_subcategories' gid='forum'}</h2></dt>
		<dt class="w50"><h2>{l i='field_author' gid='forum'}</h2></dt>
		<dt class="w50 center"><h2>{l i='field_date' gid='forum'}</h2></dt>
		<dt class="w50 center">{l i='link_messages_count' gid='forum'}</dt>
	</dl>
</div>
{foreach item=item key=key from=$subcategories}
	<div class="table-div wp100">
		<dl>
			<dt class="w100"><a href="{$site_url}forum/messages/{$item.id}">{$item.subcategory}</a></dt>
			<dt class="w50">{if $item.latest.is_admin eq 1}{l i='admin_name' gid='forum'}{else}<a href="{$site_url}users/view/{$item.latest.user.id}">{$item.latest.user.output_name}</a>{/if}</dt>
			<dt class="w50 center">{$item.latest.date_created|date_format:$page_data.date_format}</dt>
			<dt class="w50 center">{$item.messages_count}</dt>
		</dl>
	</div>
{foreachelse}
	<div class="center">{l i='no_subcategories' gid='forum'}</div>
{/foreach}
{if $categories}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}

{include file="footer.tpl"}
