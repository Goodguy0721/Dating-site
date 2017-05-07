{include file="header.tpl"}
<form method="post" action="" name="save_form" enctype="multipart/form-data">
	<div class="edit-form n150">
		<div class="row header">{l i='admin_header_comment_change' gid='blogs'}</div>
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
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
</form>
		
<script>{literal}
	$(function(){
		$("div.row:visible:odd").addClass("zebra");
	});
{/literal}</script>

{include file="footer.tpl"}
