{include file="header.tpl"}
<form method="post" action="" name="save_form" enctype="multipart/form-data">
	<div class="edit-form n150">
		<div class="row header">{l i='admin_header_post_change' gid='blogs'}</div>
		<div class="row">
			<div class="h">{l i='field_title' gid='blogs'}:&nbsp;* </div>
			<div class="v">
				<input type="text" name="title" value="{$data.title|escape}" class="long" />
			</div>
		</div>
		<div class="row">
			<div class="h">{l i='field_description' gid='blogs'}:&nbsp;* </div>
			<div class="v">
				{$content_fck}
			</div>
		</div>
		<div class="row">
			<div class="h">{l i='field_hidden_post' gid='blogs'}: </div>
			<div class="v"><input type="hidden" value="0" name="is_hidden"><input type="checkbox" value="1" name="is_hidden" {if $data.is_hidden}checked{/if}></div>
		</div>
		<div class="row">
			<div class="h">{l i='field_can_comment' gid='blogs'}: </div>
			<div class="v"><input type="hidden" value="0" name="can_comment"><input type="checkbox" value="1" name="can_comment" {if $data.can_comment}checked{/if}></div>
		</div>
		<div class="row">
			<div class="h">{l i='field_tags' gid='blogs'}:&nbsp;* </div>
			<div class="v">
				<input type="text" name="tags" value="{$data.tags_str|escape}" class="long" />
			</div>
		</div>
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a class="cancel" href="{$site_url}admin/blogs/posts/{$data.blog_id}">{l i='btn_cancel' gid='start'}</a>
</form>
		
<script>{literal}
	$(function(){
		$("div.row:visible:odd").addClass("zebra");
	});
{/literal}</script>

{include file="footer.tpl"}
