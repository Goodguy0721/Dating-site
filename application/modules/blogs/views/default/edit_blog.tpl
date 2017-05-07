{include file="header.tpl"}
<div class="content-block">
	<h1>{l i='header_edit_a_blog' gid='blogs'}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	{include file="add_blog_form.tpl" module="blogs" theme="default" data=$blog}
</div>
<div class="clr"></div>
{include file="footer.tpl"}
