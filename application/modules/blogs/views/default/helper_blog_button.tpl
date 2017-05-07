{if !$blog_id}
<a href="{seolink module='blogs' method='index'}" class="link-r-margin" title="{l i='my_blog' gid='blogs'}"><i class="icon-book icon-big edge hover"></i></a>
{else}
<a href="{seolink module='blogs' method='view_blog'}{$blog_id}" class="link-r-margin" title="{l i='view_blog' gid='blogs'}"><i class="icon-book icon-big edge hover"></i></a>
{/if}