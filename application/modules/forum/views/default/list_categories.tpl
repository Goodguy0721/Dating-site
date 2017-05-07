{include file="header.tpl"}
	<h1>{l i='title_public' gid='forum'}</h1>

{*if $categories}
	<div class="sorter short-line" id="sorter_block">
		<div class="fright">{pagination data=$page_data type='cute'}</div>
	</div>
{/if*}
<div class="table-div wp100">
	<dl>
		<dt class="w100"><h2>{l i='field_categories' gid='forum'}</h2></dt>
		<dt class="w300"><h2>{l i='field_category_description' gid='forum'}</h2></dt>
		<dt class="w50 center"><h2>{l i='link_subcategory_count' gid='forum'}</h2></dt>
		<dt class="w50 center"><h2>{l i='link_messages_count' gid='forum'}</h2></dt>
	</dl>
</div>
{foreach item=item key=key from=$categories}
	<div class="table-div wp100">
		<dl>
			<dt class="w100"><a href="{$site_url}forum/topics/{$item.id}">{$item.category}</a></dt>
			<dt class="w300">{$item.description}</dt>
			<dt class="w50 center">{$item.subcategory_count}</dt>
			<dt class="w50 center">{$item.messages_count}</dt>
		</dl>
	</div>
{foreachelse}
	<div class="center">{l i='no_categories' gid='forum'}</div>
{/foreach}
{if $categories}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}

{include file="footer.tpl"}
