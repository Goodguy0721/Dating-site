{include file="header.tpl"}
<div class="content-block">
	<h1>{if !$post.id}{l i='header_create_a_post' gid='blogs'}{else}{l i='header_edit_a_post' gid='blogs'}{/if}</h1>
	{include file="blog_menu.tpl" module="blogs" theme="default"}
	<div style="margin-top:15px">
	<form method="post" enctype="multipart/form-data">
		<div class="r">
			<div class="f">{l i='field_title' gid='blogs'}: </div>
			<div class="v"><input type='text' value='{$post.title}' name="title"></div>
		</div>
		<div class="r">
			<div class="f">{l i='field_description' gid='blogs'}: </div>
			<div class="v">{$content_fck}</div>
		</div>
		<div class="r">
			<div class="v">
				<input type="hidden" value="0" name="is_hidden"><input type="checkbox" value="1" name="is_hidden" {if $post.is_hidden}checked{/if}>
				<label for="is_hidden">{l i='field_hidden_post' gid='blogs'}</label>
			</div>
		</div>
		<div class="r">
			<div class="v">
				<input type="hidden" value="0" name="can_comment"><input type="checkbox" value="1" name="can_comment" {if $post.can_comment}checked{/if}>
				<label for="is_hidden">{l i='field_can_comment' gid='blogs'}</label>
			</div>
		</div>
		<div class="r">
			<div class="f">{l i='field_tags' gid='blogs'}: </div>
			<div class="v"><input type='text' value='{$post.tags_str}' name="tags"></div>
		</div>
		<div class="r">
			<div class="f">&nbsp;</div>
			<div class="v">
				<input type="submit" value="{if !$post.id}{l i='btn_add' gid='start' type='button'}{else}{l i='btn_save' gid='start' type='button'}{/if}" name="btn_save">
			</div>
		</div>
	</form>
</div>
</div>
<div class="clr"></div>
{include file="footer.tpl"}
