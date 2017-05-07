<div class="edit_block">
	<div class="view-user">
	<h2 class="line top bottom linked">
		{l i='category_search_result' gid='blogs'} {$category_name}
		<a class="fright mr5" href="javascript:void(0)" onclick="document.location='{seolink module='blogs' method='categories'}'"><i class="icon-list icon-big edge hover" title="{l i='link_list_categories' gid='blogs'}"></i></a>
	</h2>
		{foreach item=item from=$blogs}
		<div style="overflow:hidden;padding: 5px 0;" class="line bottom">
				<div class="fleft" style="width: 120px;">
				<a class="imgcanvas_container" href="{$item.user.link}" target="_blank">
					<img src="{$item.user.media.user_logo.thumbs.middle}" class="imgcanvas" alt="">
				</a>
			</div>
			<div class="fleft" style="width: 799px;">
				<div class="fleft">
					<h4>
						<a href="{$site_url}blogs/view_blog/{$item.id}" style="font-weight:bold">{$item.title}</a>
					</h4>
					<p style="overflow:hidden;">
						<font class="fleft">{l i='post_author' gid='blogs'}:&nbsp;</font>
						<a class="fleft" href="{$item.user.link}">{$item.user.output_name}</a>
					</p>
					<p class="text_hidden" style="overflow:hidden;">
						<font class="fleft">{l i='posted_at' gid='blogs'}:&nbsp;</font>
						<font class="fleft">{$item.date_created|date_format:$page_data.date_time_format}</font>
						{if $item.is_hidden eq 1}<font class="fleft">&nbsp;&nbsp;&nbsp;{$lang.blog.hidden_post}</font>{/if}
					</p>
				</div>		
				
				<div class="fright sharing soc">						
					<table>
						<tr>
							<td width="136">
								<a target="_blank" class="mrc__plugin_uber_like_button" href="http://connect.mail.ru/share?url={$site_url}blogs/view_blog/{$item.id}" data-mrc-config="{literal}{'nt' : '1', 'cm' : '1', 'ck' : '1', 'sz' : '20', 'st' : '3'}{/literal}">
									Send
								</a>
							</td>
							<td>
								<div class="fb-like" data-href="{$site_url}blogs/view_blog/{$item.id}" data-send="false" data-layout="button_count" data-width="150" data-show-faces="false"></div>
							</td>
							<td>
								<a href="https://twitter.com/share" class="twitter-share-button" data-url="{$site_url}blogs/view_blog/{$item.id}" data-count="horizontal" data-text="{$item.title}" data-via="" data-lang="en"></a>
							</td>
							<td>
								<span class="g">
									<g:plusone href="{$site_url}blogs/view_blog/{$item.id}"></g:plusone>
								</span>
							</td>
						</tr>
					</table>						
				</div>
				
				<script>{literal}
					loadScripts(["http://cdn.connect.mail.ru/js/loader.js", "//platform.twitter.com/widgets.js", ],
						function(){},'',{async: false}
					);
				</script>{/literal}
				
				<div class="blog_body" style="display: table; width: 100%;padding: 10px 0;">
					<div class="mb5">{$item.body|truncate:500}</div>
					<p>
						{if $item.can_comment eq '1' || $is_user eq 1}
						{if $item.comments_count}
							<a href="{$site_url}blogs/view_post/{$item.id}">{$item.comments_count} {l i='comments' gid='blogs'}</a>
						{else}
							<font class="text_hidden">{l i='no_comment' gid='blogs'}</font>
						{/if}
						&nbsp;&nbsp;|&nbsp;&nbsp;					
							<a href="{$site_url}blogs/view_post/{$item.id}">{l i='link_add_comment' gid='blogs'}</a>
						{/if}
						{if $show_photo eq '1'}&nbsp;										
						{else}
							{if $is_user eq '1'}
								&nbsp;&nbsp;|&nbsp;&nbsp;
								<a href="javascript:void(0)" onclick="document.location='{$site_url}blogs/edit_post/{$item.id}'" title="{l i='link_edit_post' gid='blogs'}" alt="{l i='link_edit_post' gid='blogs'}">{l i='link_edit_post' gid='blogs'}</a>&nbsp;&nbsp;|&nbsp;&nbsp;
								<a href="javascript:void(0)" onclick="javascript: {literal}if(!confirm('{/literal}{l i='note_delete_post' gid='blogs' type='js'}{literal}')) {return false;}else{document.location='{/literal}{$site_url}blogs/delete_post/{$item.id}{literal}'}{/literal}" title="{l i='link_delete_post' gid='blogs'}" alt="{l i='link_delete_post' gid='blogs'}">{l i='link_delete_post' gid='blogs'}</a>
							{/if}
						{/if}
					</p>
				</div>
			</div>
		</div>
		{foreachelse}
			<div class="center">{l i='no_blogs' gid='blogs'}</div>
		{/foreach}
		{if $blogs}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}	
	</div>
</div>