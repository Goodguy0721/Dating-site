{include file="header.tpl"}
{helper func_name=get_admin_level1_menu helper_name=menu func_param='admin_questions_menu'}

<form method="post" name="deleteForm" action="{$site_url}admin/questions/delete_user_question" enctype="multipart/form-data">

<div class="actions">
	<ul>		
		<li><div class="l"><input onclick="deleteSelectBlock()" type="button" value="{l i='delete_selected' gid='questions'}"></div></li>
	</ul>
	&nbsp;
</div>

<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><input type="checkbox" onclick="javascript: checkAll(this.checked);"></th>		
		<th>{l i='user_from' gid='questions'}</th>
		<th>{l i='user_to' gid='questions'}</th>		
		<th>{l i='field_name' gid='questions'}</th>
		<th>&nbsp;</th>
	</tr>
	{foreach from=$questions item=question}
		{counter print=false assign=counter}
		<tr{if $counter is div by 2} class="zebra"{/if}>
			<td class="first w20 center">
				<input class="check-del" type="checkbox" name="delete_questions[{$question.id}]">			
			</td>			
			<td>{$question.user_from}</td>
			<td>{$question.user_to}</td>			
			<td>{$question.name}</td>
			<td class="w150 icons">				
				<a onclick="javascript: if(!confirm('{l i='admin_confirm_delete' gid='questions'}')) return false;" href="{$site_url}admin/questions/delete_user_question/{$question.id}"><img width="16" height="16" border="0" title="{l i='link_delete_question' gid='questions'}" alt="{l i='link_delete_question' gid='questions'}" src="{$site_root}{$img_folder}icon-delete.png"></a>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="8" class="center">{l i='no_questions' gid='questions'}</td></tr>
	{/foreach}
</table>

</form>

{include file="pagination.tpl"}

<script type='text/javascript'>{literal}
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
