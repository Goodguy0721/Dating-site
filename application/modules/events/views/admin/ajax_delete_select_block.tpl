<div class="load_content_controller">
	<div class="inside">
		
		<form id="delete_user" class="edit-form n100" action="{$data.action}" method="post" enctype="multipart/form-data"  >
			
			<h1>{l i='success_text_delete' gid='media'}</h1>
			
			<div class="btn">
				<div class="l">
					<input type="submit" id="lie_delete" name="btn_confirm" value="{l i='btn_confirm' gid='media'}" >
				</div>
			</div>
			
		</form>
		
	</div>
</div>

<script type="text/javascript">
	{literal}
		$(function(){
			
			$('#delete_user').unbind('submit').on('submit', function(e){
				e.preventDefault();
                                
                                var checked = $('input[type=checkbox].grouping').is(':checked');
                                if(checked){
                                    $('#participants_form').attr('action', '{/literal}{$action}{literal}').submit();
                                }
			});
		});
	{/literal}
</script>
