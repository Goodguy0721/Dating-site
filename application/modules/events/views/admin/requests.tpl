{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_events_menu'}
{if $participants_count > 0}
<div class="actions">
	<ul>
        <li>
            <div class="l">
                <a href="{$site_url}admin/events/deleteParticipantSelect/" id="delete_selected">
                    {l i='link_delete_selected' gid='events'}
                </a>
            </div>
        </li>
	</ul>&nbsp;
</div>
{/if}
<form id="participants_form" action="" method="post">
<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><input type="checkbox" id="grouping_all"></th>
		<th class="w150"><a href="{$sort_links.name}"{if $order eq 'name'} class="{$order_direction|lower}"{/if}>{l i='field_name' gid='events'}</a></th>
		<th class="w150"><a href="{$sort_links.event_name}"{if $order eq 'event_name'} class="{$order_direction|lower}"{/if}>{l i='field_event_name' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.status}"{if $order eq 'status'} class="{$order_direction|lower}"{/if}>{l i='field_status' gid='events'}</a></th>
		<th class="w150"><a href="{$sort_links.date}"{if $order eq 'date'} class="{$order_direction|lower}"{/if}>{l i='field_date' gid='events'}</a></th>
		<th class="w150">{l i='field_actions' gid='events'}</th>
	</tr>  
	{foreach from=$participants item=participant}
		{counter print=false assign=counter}
		<tr {if $counter is div by 2} class="zebra"{/if}>
			<td class="first w20 center"><input type="checkbox" class="grouping" value="{$participant.id}" name="ids[]"></td>
			<td class="left"><a href="admin/users/edit/personal/{$participant.fk_user_id}">{$participant.user.nickname}</a><br>{$participant.user.fname} {$participant.user.sname}</td>
			<td class="left"><a href="admin/events/edit_main/{$participant.fk_event_id}">{$participant.event[$event_name]}</a></td>
			<td class="center">
                {$participant.status}             
            </td>
            <td class="center">{$participant.response_date}</td>
			<td class="icons">
				<div>
                    {if $participant.status eq 'pending'}
                        <a href="{$site_url}admin/events/participant_status/{$participant.id}/approved"><img src="/application/views/admin/img/icon-approve.png" width="13" height="13" border="0" alt="{l i='link_accept_participant' gid='events'}" title="{l i='link_accept_participant' gid='events'}"></a>
                        <a href="{$site_url}admin/events/participant_status/{$participant.id}/declined"><img src="/application/views/admin/img/icon-decline.png" width="13" height="13" border="0" alt="{l i='link_decline_participant' gid='events'}" title="{l i='link_decline_participant' gid='events'}"></a>
                    {elseif $participant.status eq 'approved'}
                        <a href="{$site_url}admin/events/participant_status/{$participant.id}/declined"><img src="/application/views/admin/img/icon-decline.png" width="13" height="13" border="0" alt="{l i='link_decline_participant' gid='events'}" title="{l i='link_decline_participant' gid='events'}"></a>
                    {else}
                        <a href="{$site_url}admin/events/participant_status/{$participant.id}/approved"><img src="/application/views/admin/img/icon-approve.png" width="13" height="13" border="0" alt="{l i='link_accept_participant' gid='events'}" title="{l i='link_accept_participant' gid='events'}"></a>
                    {/if}
                    <a href="{$site_url}admin/tickets/answer/{$participant.id}"><i title="{l i='link_connect_participant' gid='events'}" class="fa fa-envelope-o"></i></a>
                    <a title="{l i='link_remind_participant' gid='events'}" href="{$site_url}admin/events/remind_participant/{$participant.id}"><i class="fa fa-clock-o"></i></a>                    
					<a href="{$site_url}admin/events/deleteParticipant/{$participant.id}">
						<i class="fa fa-trash"></i>
					</a>                        
                </div>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="6" class="center">{l i='no_participants' gid='events'}</td></tr>
	{/foreach}
</table>
</form>
{include file="pagination.tpl"}
<script type="text/javascript">
{literal}
$(function(){
	$('#grouping_all').bind('click', function(){
		var checked = $(this).is(':checked');
		if(checked){
			$('input.grouping').prop('checked', true);
		}else{
			$('input.grouping').prop('checked', false);
		}
	});
	$('#delete_selected').bind('click', function(){
		if(!$('input[type=checkbox].grouping').is(':checked')) return false; 
		if(!confirm('{/literal}{l i='note_alerts_delete_all' gid='events' type='js'}{literal}')) return false;
		$('#participants_form').attr('action', $(this).attr('href')).submit();		
		return false;
	});
});
{/literal}</script>
{include file="footer.tpl"}