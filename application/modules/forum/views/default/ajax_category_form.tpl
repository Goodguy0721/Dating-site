<div class="content-block load_content">
	<h1>{l i='header_category' gid='forum'}</h1>
	<div class="inside">
		<form action="{$site_url}forum/edit_group_category/{$group_id}/{$category.id}" method="post">
			<div class="r">
				<div class="f">{l i='field_category_name' gid='forum'}: </div>
				<div class="v">
					<input type="text" name="category" value="{$category.category}" style="width:450px">
				</div>
			</div>
			<div class="r">
				<div class="f">{l i='field_category_description' gid='forum'}: </div>
				<div class="v"><textarea name="description" style="height:100px;width:450px">{$category.description}</textarea></div>
			</div>
			<div class="r">
				<input type="submit" value="{l i='btn_save' gid='start' type='button'}" name="btn_save"/>
			</div>
		</form>
	</div>
	<div class="clr"></div>
</div>