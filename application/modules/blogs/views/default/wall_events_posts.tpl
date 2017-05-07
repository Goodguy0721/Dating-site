{strip}
<div>
	{if $event.id_poster == $user_id}
		<a class="fright delete_wall_event" data-id="{$event.id}" data-message="{l i=confirm_delete gid=wall_events}" href="javascript:;">{l i='btn_delete' gid='start'}</a>
	{/if}
</div>
{foreach from=$event.data item='edata' key='key'}
	{assign var='id_post' value=$edata.id_post}
	{if !$posts[$id_post]}{assign var='id_post' value='0'}{/if}
	<div>{$edata.event_date|date_format:$date_format}</div>
	<div>
		<div class="ptb5">
			{if $event.event_type_gid eq 'blog_post_created'}
				<a href="{$posts[$id_post].user.link}">{$posts[$id_post].user.output_name}</a> {l i='wall_created_post' gid='blogs'} <a href="{$site_url}blogs/view_post/{$id_post}">{l i='post' gid='blogs'}</a><br/>
				{l i='field_title' gid='blogs'}: {$posts[$id_post].blog.title}: {$posts[$id_post].title}
			{/if}
		</div>
	</div>
{/foreach}
{/strip}