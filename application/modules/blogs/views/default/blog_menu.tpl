<div class="tabs tab-size-15 noPrint">
	<ul>
		<li{if $action eq 'my_blog'} class="active"{/if}><a data-pjax-no-scroll="1" href="{seolink module='blogs' method='index'}">{if $menu_first_tab_name}{$menu_first_tab_name}{else}{l i='header_my_blog' gid='blogs'}{/if}</a></li>
		<li{if $action eq 'calendar'} class="active"{/if}><a data-pjax-no-scroll="1" href="{seolink module='blogs' method='calendar'}">{l i='header_blog_calendar' gid='blogs'}</a></li>
		<li{if $action eq 'friends'} class="active"{/if}><a data-pjax-no-scroll="1" href="{seolink module='blogs' method='friends'}">{l i='header_blog_friends' gid='blogs'}</a></li>
		<li{if $action eq 'categories'} class="active"{/if}><a data-pjax-no-scroll="1" href="{seolink module='blogs' method='categories'}">{l i='header_blog_categories' gid='blogs'}</a></li>
	</ul>
</div>

