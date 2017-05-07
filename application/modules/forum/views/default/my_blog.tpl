{include file="header.tpl"}
<div class="content-block">
	<h1>{if $create_form eq '1'}{l i='header_create_a_blog' gid='blogs'}{else}{l i='header_my_blog' gid='blogs'}{/if}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	{if $create_form eq '1'}
		{include file="add_blog_form.tpl" module="blogs" theme="default"}
	{else}
		{include file="block_posts_list.tpl" module="blogs" theme="default" is_user=1}
	{/if}
</div>
<div class="clr"></div>
{include file="footer.tpl"}
