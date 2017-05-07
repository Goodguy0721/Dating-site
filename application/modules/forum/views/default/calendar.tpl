{include file="header.tpl"}
{literal}
<style>
.title_big_calendar {
    display: table;
    padding: 10px 20px;
    width: 95%;
}
.big_calendar {width: 100%; font-size: 11px; }
.big_calendar .white td {
    border: 1px solid #fcf4ec;
}
.big_calendar .white td {
    padding-left: 0;
}
.big_calendar td, .small_calendar td {
    border: 1px solid #ffffff;
}
.big_calendar td {
    padding: 4px 0;
    width: 14%;
}
.big_calendar .white td {
    padding: 13px 0;
    text-align: center;
    width: 14%;
}
.big_calendar span {
    padding-left: 8px;
}
.small_calendar .current_day, .big_calendar .current_day {
    color: #ffffff;
}
</style>
{/literal}
<div class="content-block">
<h1>{l i='header_blog' gid='blogs'}</h1>
{include file="blog_menu.tpl" module="blogs" theme="default"}
	
<div class="title_big_calendar">
	<a class="small-left-arr" href="{$site_url}blogs/calendar/month/back_month/{$selected_month.year}/{$selected_month.mon}" title="{$prev_mounth}"><i class="icon-arrow-left"></i></a>&nbsp;
	{$selected_month.month},&nbsp;{$selected_month.year}
	&nbsp;<a href="{$site_url}blogs/calendar/month/next_month/{$selected_month.year}/{$selected_month.mon}" title="{$next_mounth}"><i class="icon-arrow-right"></i></a>
</div>
<!-- /Calendar menu -->
<table class="big_calendar">
	<tr class="white bg-highlight_bg">
		<td>{l i='monday' gid='blogs'}</td>
		<td>{l i='tuesday' gid='blogs'}</td>
		<td>{l i='wednesday' gid='blogs'}</td>
		<td>{l i='thursday' gid='blogs'}</td>
		<td>{l i='friday' gid='blogs'}</td>
		<td>{l i='saturday' gid='blogs'}</td>
		<td>{l i='sunday' gid='blogs'}</td>
	</tr>
	{foreach from=$current_month item=week}
		<tr>
			<td {if $week.1.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.1 == "false"}
					&nbsp;
				{else}
					<span>{$week.1.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.1.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>

			<td {if $week.2.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.2 == "false"}
					&nbsp;
				{else}
					<span>{$week.2.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.2.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
			<td {if $week.3.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.3 == "false"}
					&nbsp;
				{else}
					<span>{$week.3.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.3.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
			<td {if $week.4.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.4 == "false"}
					&nbsp;
				{else}
					<span>{$week.4.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.4.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
			<td {if $week.5.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.5 == "false"}
					&nbsp;
				{else}
					<span>{$week.5.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.5.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
			<td {if $week.6.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.6 == "false"}
					&nbsp;
				{else}
					<span>{$week.6.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.6.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
			<td {if $week.0.current_day == 'true'}class="current_day bg-main_bg"{/if}>
				{if $week.0 == "false"}
					&nbsp;
				{else}
					<span>{$week.0.mday}</span><div style="padding-top:8px;">&nbsp;</div>
					{foreach from=$week.0.blog item=blog}
						<a href="{$blog.post_link}">
							{$blog.title|truncate:20}
						</a><br />
					{/foreach}
				{/if}
			</td>
		</tr>
	{/foreach}
</table>
</div>
<div class="clr"></div>
{include file="footer.tpl"}
