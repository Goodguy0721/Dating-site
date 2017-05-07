{include file="header.tpl"}
<div class="content-block">
	<h1>{$blog.title}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	<h2 class="line top bottom linked">
		<a href="{$site_url}blogs/view_blog/{$blog.id}">{$blog.title}</a>
		{if $blog.user_id eq $user_session_data.user_id}
			<a class="fright" href="javascript:void(0)" onclick="javascript: {literal}if(!confirm('{/literal}{l i='note_delete_post' gid='blogs' type='js'}{literal}')) {return false;}else{document.location='{/literal}{$site_url}blogs/delete_post/{$post.id}{literal}'}{/literal}"><i class="icon-trash icon-big edge hover" title="{l i='link_delete_post' gid='blogs'}"></i></a>
			<a class="fright mr5" href="javascript:void(0)" onclick="document.location='{seolink module='blogs' method='edit_post'}{$post.id}'"><i class="icon-pencil icon-big edge hover" title="{l i='link_edit_post' gid='blogs'}"></i></a>
			<a class="fright mr5" href="javascript:void(0)" onclick="document.location='{seolink module='blogs' method='edit_post'}'"><i class="icon-plus icon-big edge hover" title="{l i='link_add_post' gid='blogs'}"></i></a>
		{/if}
	</h2>
	<div>{l i='posted_at' gid='blogs'}:&nbsp;{$post.date_created|date_format:$page_data.date_time_format}</div>
	<div style="margin-top:15px; font-weight: bold">{$post.title}</div>
	<div style="margin-top:15px;">{$post.body}</div>
	{if $post.can_comment eq '1'}
		<div style="margin-top:15px;">
			<a href="javascript:void(0)" onclick="$('#post_comment_form').toggle()">{l i='link_add_comment' gid='blogs'}</a>
			<form action="{$site_url}blogs/view_post/{$post.id}" id="post_comment_form" method="POST" style="margin-top:15px;" class="hide">
				<div class="r">
					<div class="f">{l i='field_title' gid='blogs'}: </div>
					<div class="v"><input type='text' value='{$comment.title}' name="title"></div>
				</div>
				<div class="r">
					<div class="f">{l i='field_description' gid='blogs'}: </div>
					<div class="v">
						<textarea id="body" name="body" style="width: 300px; height: 200px;"></textarea>
						<script>{literal}
							var CKEDITOR_BASEPATH = '{/literal}{$site_url}system/plugins/ckeditor/{literal}';
							loadScripts(["{/literal}{$site_url}system/plugins/ckeditor/ckeditor.js{literal}"],
								function(){
									CKEDITOR.replace('body', {customConfig:'{/literal}{$site_url}system/plugins/ckeditor/config.js{literal}',language: '{/literal}{$_LANG.code}{literal}', toolbar: 
										[
											{ name: 'document', items: [ 'Source' ] },
											{ name: 'actions', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
											{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
											{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'SpecialChar' ] },
											{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
											{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
											{ name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
											{ name: 'colors', items: [ 'TextColor', 'BGColor' ] }
										]
									});
								},
								'',
								{async: false}
							);
						</script>{/literal}
					</div>
				</div>
				<div class="r">
					<div class="f">&nbsp;</div>
					<div class="v">
						<input type="submit" value="{l i='btn_add' gid='start' type='button'}" name="btn_save">
					</div>
				</div>
			</form>
		</div>
		{if $comments_count > 0}
			<h2 class="line top bottom linked">{l i='field_comments' gid='blogs'}</h2>
		{/if}
		{foreach item=item from=$comments}
			<div class="fleft" style="width: 120px;">
				<a href="{$item.user.link}" target="_blank">
					<img src="{$item.user.media.user_logo.thumbs.middle}" class="imgcanvas" alt="">
				</a>
			</div>
			<div class="fleft" style="width: 599px;">
				{$item.title} {l i='posted_by' gid='blogs'} <a href="{$item.user.link}" target="_blank">{$item.user.output_name}</a>
				<br/><br/>
				<div>{$item.body}</div>
					<a href="javascript:void(0)" onclick="$('#post_comment_form_{$item.id}').toggle();">{l i='leave_reply' gid='blogs'}</a>&nbsp;
				{if $item.user_id eq $user_session_data.user_id || $blog.user_id eq $user_session_data.user_id}
					|&nbsp;<a href="javascript:void(0)" onclick="javascript: {literal}if(!confirm('{/literal}{l i='note_delete_comment' gid='blogs' type='js'}{literal}')) {return false;}else{document.location='{/literal}{$site_url}blogs/delete_comment/{$item.id}{literal}'}{/literal}">{l i='link_delete_comment' gid='blogs'}</a>
				{/if}
			</div>
			<div class="clr"></div>
			<form action="{$site_url}blogs/view_post/{$post.id}" id="post_comment_form_{$item.id}" class="hide" method="POST" style="margin-top:15px;">
				<input type="hidden" name="comment_id" value="{$item.id}">
				<div class="r">
					<div class="f">{l i='field_title' gid='blogs'}: </div>
					<div class="v"><input type='text' value="{l i='reply' gid='blogs'} {$item.user.output_name}" name="title"></div>
				</div>
				<div class="r">
					<div class="f">{l i='field_description' gid='blogs'}: </div>
					<div class="v">
						<textarea id="body{$item.id}" name="body" style="width: 300px; height: 200px;"></textarea>
						<script>{literal}
							var CKEDITOR_BASEPATH = '{/literal}{$site_url}system/plugins/ckeditor/{literal}';
							loadScripts(["{/literal}{$site_url}system/plugins/ckeditor/ckeditor.js{literal}"],
								function(){
									CKEDITOR.replace('body{/literal}{$item.id}{literal}', {customConfig:'{/literal}{$site_url}system/plugins/ckeditor/config.js{literal}',language: '{/literal}{$_LANG.code}{literal}', toolbar: 
										[
											{ name: 'document', items: [ 'Source' ] },
											{ name: 'actions', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
											{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
											{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'SpecialChar' ] },
											{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ], items: [ 'Bold', 'Italic', 'Underline', 'Strike', '-', 'RemoveFormat' ] },
											{ name: 'paragraph', groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ], items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
											{ name: 'styles', items: [ 'Format', 'Font', 'FontSize' ] },
											{ name: 'colors', items: [ 'TextColor', 'BGColor' ] }
										]
									});
								},
								'',
								{async: false}
							);
						</script>{/literal}
					</div>
				</div>
				<div class="r">
					<div class="f">&nbsp;</div>
					<div class="v">
						<input type="submit" value="{l i='btn_add' gid='start' type='button'}" name="btn_save">
					</div>
				</div>
			</form>
		{/foreach}
		<div class="clr"></div>
		{if $comments}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}
	{/if}
</div>
<div class="clr"></div>
{include file="footer.tpl"}