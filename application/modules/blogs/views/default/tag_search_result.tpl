{if $blogs || $posts}
<h2 class="line top bottom linked">
	{l i='tags_search_result' gid='blogs'} - {$tag}
</h2>
{foreach from=$blogs item=item}
	<div class="fleft" style="width:45%; padding:5px 30px 5px 0px"><a href="{$site_url}blogs/view_blog/{$item.id}">{$item.title}</a><span class="fright">{l i='post_author' gid='blogs'}: {$item.user.output_name}</span></div>
{/foreach}
{foreach from=$posts item=item}
	<div class="fleft" style="width:45%; padding:5px 30px 5px 0px"><a href="{$site_url}blogs/view_post/{$item.id}">{$item.title}</a><span class="fright">{l i='post_author' gid='blogs'}: {$item.user.output_name}</span></div>
{/foreach}
{/if}