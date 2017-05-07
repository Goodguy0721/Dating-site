{include file="header.tpl"}
<div class="content-block">
	<h1>{l i='header_blog' gid='blogs'}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	{include file="block_posts_list.tpl" module="blogs" theme="default" is_user="2"}
</div>
<div class="clr"></div>
{include file="footer.tpl"}
