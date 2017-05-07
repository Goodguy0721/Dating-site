{include file="header.tpl"}
{helper func_name=get_admin_level1_menu helper_name=menu func_param='admin_questions_menu'}

<form method="post" name="deleteForm" action="{$site_url}admin/questions/delete_admin_question" enctype="multipart/form-data">

{if $back ne 'user'}
<div class="actions">
	<ul>
		<li><div class="l"><a href="{$site_url}admin/questions/edit_question">{l i='add_question' gid='questions'}</a></div></li>
		<li><div class="l"><input onclick="deleteSelectBlock()" type="button" value="{l i='delete_selected' gid='questions'}"></div></li>
	</ul>
	&nbsp;
</div>
{/if}

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><input type="checkbox" onclick="javascript: checkAll(this.checked);"></th>		
		<th>{l i='field_name' gid='questions'}</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$questions item=question}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first w20 center">
				<input class="check-del" type="checkbox" name="delete_questions[{$question.id}]">			
			</td>
			{if $back eq 'user'}
			<td>{$question.username}</td>			
			{/if}
			<td>{$question.name}</td>
			<td class="w150 icons">
				<span>
					<a href="javascript:void(0);" onclick="activateQuestion('{$question.id|escape:javascript}', 0, this);" {if !$question.status}style="display:none;"{/if}><img src="{$site_root}{$img_folder}icon-full.png" width="16" height="16" alt="{l i='link_deactivate_question' gid='question'}" title="{l i='link_deactivate_question' gid='question'}"></a>
					<a href="javascript:void(0);" onclick="activateQuestion('{$question.id|escape:javascript}', 1, this);" {if $question.status}style="display:none;"{/if}><img src="{$site_root}{$img_folder}icon-empty.png" width="16" height="16" alt="{l i='link_activate_question' gid='question'}" title="{l i='link_activate_question' gid='question'}"></a>
				</span>
				<a href='{$site_url}admin/questions/edit_question/{$question.id}/{$back}'><img src="{$site_root}{$img_folder}icon-edit.png" width="16" height="16" alt="{l i='link_edit_question' gid='questions'}" title="{l i='link_edit_question' gid='questions'}"></a>
				<a onclick="javascript: if(!confirm('{l i='admin_confirm_delete' gid='questions'}')) return false;" href="{$site_url}admin/questions/delete_admin_question/{$question.id}/{$back}"><img width="16" height="16" border="0" title="{l i='link_delete_question' gid='questions'}" alt="{l i='link_delete_question' gid='questions'}" src="{$site_root}{$img_folder}icon-delete.png"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="8" class="center">{l i='no_questions' gid='questions'}</td></tr>
	{/foreach}
</table>
</form>

{include file="pagination.tpl"}

<script type='text/javascript'>
{literal}
	function activateQuestion(id, status, a_obj){
		$.post(
			site_url+'admin/questions/ajax_activate_question/',
			{id: id, status: status},
			function(resp){
				if(resp.status){
					$(a_obj).parent().find('a:hidden').show();
					$(a_obj).hide();
					if (status==1) {
						 error_object.show_error_block("{/literal}{l i='question_activated' gid='questions'}{literal}", 'success');
					}else{
						 error_object.show_error_block("{/literal}{l i='question_deactivated' gid='questions'}{literal}", 'success');
					}
					
				}
			},
			'json'
		);
	}
{/literal}
</script>

<script>{literal}
	function checkAll(checked){
		if(checked)
			$('.check-del').prop('checked', true);
		else
			$('.check-del').prop('checked', false);
	}
	
	
	function deleteSelectBlock(){
		var data = new Array();
		$('.check-del:checked').each(function(i){
			data[i] = $(this).val();
		});
		if(data.length > 0){
			if(!confirm({/literal}'{l i='admin_mass_confirm_delete' gid='questions'}'{literal})) return false;
			deleteForm.submit();			
		}else{
			error_object.show_error_block({/literal}'{l i='no_questions_selected' gid='questions'}'{literal}, 'error');			
		}
	}	
	
{/literal}</script>



{include file="footer.tpl"}