{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
{helper func_name='get_admin_level1_menu' helper_name='menu' func_param='admin_events_menu'}
<div class="actions">
	<ul>
		<li>
			<div class="l">
				<a href="{$site_url}admin/events/edit_main">
					{l i='link_add' gid='events'}
				</a>
			</div>
		</li>
		{if $events}
			<li>
				<div class="l">
					<a href="{$site_url}admin/events/deleteSelect/" id="delete_selected">
						{l i='link_delete_selected' gid='events'}
					</a>
				</div>
			</li>
		{/if}
	</ul>&nbsp;
</div>
<div class="menu-level3">
	<ul>
		<li class="{if $filter eq 'all'}active{/if}{if !$filter_data.all} hide{/if}"><a href="{$site_url}admin/events/index/all">{l i='filter_all_events' gid='events'} ({$filter_data.all})</a></li>
		<li class="{if $filter eq 'admin'}active{/if}{if !$filter_data.not_active} hide{/if}"><a href="{$site_url}admin/events/index/admin">{l i='filter_admin_events' gid='events'} ({$filter_data.admin})</a></li>
		<li class="{if $filter eq 'users'}active{/if}{if !$filter_data.active} hide{/if}"><a href="{$site_url}admin/events/index/users">{l i='filter_user_events' gid='events'} ({$filter_data.users})</a></li>
        </ul>
	&nbsp;
</div>

<form id="events_form" action="" method="post">
<table cellspacing="0" cellpadding="0" class="data" width="100%">
	<tr>
		<th class="first"><input type="checkbox" id="grouping_all"></th>
		<th class="w300"><a href="{$sort_links.name}"{if $order eq 'name'} class="{$order_direction|lower}"{/if}>{l i='field_name' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.category}"{if $order eq 'category'} class="{$order_direction|lower}"{/if}>{l i='field_category' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.date_created}"{if $order eq 'date_created'} class="{$order_direction|lower}"{/if}>{l i='field_date_created' gid='events'}</a></th>
		<th class="w100"><a href="{$sort_links.author}"{if $order eq 'author'} class="{$order_direction|lower}"{/if}>{l i='field_author' gid='events'}</a></th>
		<th class="w100">{l i='field_actions' gid='events'}</th>
	</tr>
	{foreach from=$events item=event}
		{counter print=false assign=counter}
		<tr {if $counter is div by 2} class="zebra"{/if}>
			<td class="first w20 center"><input type="checkbox" class="grouping" value="{$event.id}" name="ids[]"></td>
			<td class="center">{$event.name}</td>
			<td class="center">
                            {ld gid='events' i='category'}                
                            {foreach item=item key=key from=$ld_category.option}
                                {if $key eq $event.category}{$item}{/if}
                            {/foreach} 
                        </td>
                        <td class="center"> 
                            {$event.date_created}
                        </td>
                        <td class="center"> 
                            {$event.user.output_name}
                        </td>
			<td class="icons">
				<div>
					{if $event.is_active}
						<a href="{$site_url}admin/events/activate/{$event.id}/0"><i class="fa fa-circle"></i></a>
					{else}
						<a href="{$site_url}admin/events/activate/{$event.id}/1"><i class="fa fa-circle inactive"></i></a>
					{/if}
					<a title="{l i='link_edit_event' gid='events'}" href="{$site_url}admin/events/edit_main/{$event.id}"><i class="fa fa-pencil"></i></a>
					<a onclick="javascript: if(!confirm('{l i='note_alert_delete_event' gid='events' type='js'}')) return false;" href="{$site_url}admin/events/delete/{$event.id}">
						<i class="fa fa-trash"></i>
					</a>
				</div>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="5" class="center">{l i='no_events' gid='events'}</td></tr>
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
		$('#events_form').attr('action', $(this).attr('href')).submit();		
		return false;
	});
});
{/literal}</script>
{include file="footer.tpl"}