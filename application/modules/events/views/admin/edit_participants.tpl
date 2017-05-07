{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
<div class="menu-level2">
    <ul>
        <li><div class="l"><a id="events_edit_main_item" href="{$site_url}admin/events/edit_main/{$event_id}">{l i='menu_edit_main_item' gid='events'}</a></div></li>
        <li class="active"><div class="l"><a id="events_edit_participants_item" href="{$site_url}admin/events/edit_participants/{$event_id}">{l i='menu_edit_participants_item' gid='events'}</a></div></li>
        <li><div class="l"><a id="events_edit_album_item" href="{$site_url}admin/events/media_list/{$event_id}">{l i='menu_edit_album_item' gid='events'}</a></div></li>
    </ul>
</div>
<div class="actions">
	<ul>
            <li>
                <div class="l">
                    <a href="{$site_url}admin/events/add_participants/{$event_id}">
                        {l i='link_invite_participant' gid='events'}
                    </a>
                </div>
            </li>
            {if $participants_count > 0}
                <li>
                    <div class="l">
                        <a href="{$site_url}admin/events/deleteParticipantSelect/" id="delete_selected">
                            {l i='link_delete_selected' gid='events'}
                        </a>
                    </div>
                </li>
            {/if}
	</ul>&nbsp;
</div>
<div class="menu-level3">
	<ul>
		<li class="{if $filter eq 'all'}active{/if}{if !$filter_data.all} hide{/if}"><a href="{$site_url}admin/events/edit_participants/{$event_id}/all">{l i='filter_all_events' gid='events'} ({$filter_data.all})</a></li>
		<li class="{if $filter eq 'approved'}active{/if}{if !$filter_data.approved} hide{/if}"><a href="{$site_url}admin/events/edit_participants/{$event_id}/approved">{l i='filter_approved_events' gid='events'} ({$filter_data.approved})</a></li>
		<li class="{if $filter eq 'pending'}active{/if}{if !$filter_data.pending} hide{/if}"><a href="{$site_url}admin/events/edit_participants/{$event_id}/pending">{l i='filter_pending_events' gid='events'} ({$filter_data.pending})</a></li>
		<li class="{if $filter eq 'declined'}active{/if}{if !$filter_data.declined} hide{/if}"><a href="{$site_url}admin/events/edit_participants/{$event_id}/declined">{l i='filter_declined_events' gid='events'} ({$filter_data.declined})</a></li>
<!--		<li class="{if $filter eq 'not_responded'}active{/if}{if !$filter_data.not_responded} hide{/if}"><a href="{$site_url}admin/events/edit_participants/{$event_id}/not_responded">{l i='filter_not_responded_events' gid='events'} ({$filter_data.not_responded})</a></li>-->
        </ul>
	&nbsp;
</div>

<form id="participants_form" action="" method="post">
<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><input type="checkbox" id="grouping_all"></th>
		<th class="w300"><a href="{$sort_links.name}"{if $order eq 'name'} class="{$order_direction|lower}"{/if}>{l i='field_name' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.status}"{if $order eq 'status'} class="{$order_direction|lower}"{/if}>{l i='field_status' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.response_date}"{if $order eq 'response_date'} class="{$order_direction|lower}"{/if}>{l i='field_response_date' gid='events'}</a></th>
		<th class="w150">{l i='field_actions' gid='events'}</th>
	</tr>  
	{foreach from=$participants item=participant}
		{counter print=false assign=counter}
		<tr {if $counter is div by 2} class="zebra"{/if}>
			<td class="first w20 center"><input type="checkbox" class="grouping" value="{$participant.id}" name="ids[]"></td>
			<td class="left"><a href="{$site_url}admin/users/edit/personal/{$participant.fk_user_id}">{$participant.user.nickname}</a><br>{$participant.user.fname} {$participant.user.sname}</td>
			<td class="center">
                {$participant.status}             
            </td>
            <td class="center">{$participant.response_date}</td>
			<td class="icons">
				<div>
                    {if $participant.status eq 'pending'}
                        <a href="{$site_url}admin/events/participant_status/{$event_id}/{$participant.user.id}/approved"><img src="{$site_root}{$img_folder}icon-approve.png" width="13" height="13"  alt="{l i='link_accept_participant' gid='events'}" title="{l i='link_accept_participant' gid='events'}"></a>
                        <a href="{$site_url}admin/events/participant_status/{$event_id}/{$participant.user.id}/declined"><img src="{$site_root}{$img_folder}icon-decline.png" width="13" height="13"  alt="{l i='link_decline_participant' gid='events'}" title="{l i='link_decline_participant' gid='events'}"></a>
                    {elseif $participant.status eq 'approved'}
                    {else}
                    {/if}
                    <a href="{$site_url}admin/tickets/answer/{$participant.user.id}"><i title="{l i='link_connect_participant' gid='events'}" class="fa fa-envelope-o"></i></a>
                    <a title="{l i='link_remind_participant' gid='events'}" href="{$site_url}admin/events/remindParticipant/{$participant.user.id}"><i class="fa fa-clock-o"></i></a>                    
                    <a class="delete-user-btn" href="{$site_url}admin/events/deleteParticipant/{$participant.id}">
						<i class="fa fa-trash"></i>
					</a>                        
                </div>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="5" class="center">{l i='no_participants' gid='events'}</td></tr>
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

        $('.delete-user-btn').off().on('click', function(){
                if(!confirm('{/literal}{l i='note_delete_user' gid='users' type='js'}{literal}')) return false;
        });
        
        $('#delete_selected').off().on('click', function(){
		if(!$('input[type=checkbox].grouping').is(':checked')) return false; 
		if(!confirm('{/literal}{l i='note_alert_delete_participants_all' gid='events' type='js'}{literal}')) return false;
		$('#participants_form').attr('action', $(this).attr('href')).submit();		
		return false;
	});
});
{/literal}</script>
{include file="footer.tpl"}