{include file="header.tpl"}
<form method="post" enctype="multipart/form-data">
        <div class="actions">
            <ul>
                <li>
                    <div class="l">
    <!--                    <a id="users_link_add" href="{$site_url}admin/users/edit/personal/">
                            {l i='link_add_user' gid='users'}
                        </a>-->
                        <input type="submit" name="save" value="{l i='link_invite_participant' gid='events'}">
                    </div>
                </li>
            </ul>
            &nbsp;
        </div>
	<div class="filter-form">
		<div class="row">
			<div class="h">{l i='user_type' gid='users'}:</div>
			<div class="v">
				<select name="user_type"  class="middle_long">
					<option value="all"{if $user_type=='all'} selected{/if}>...</option>
					{foreach from=$user_types.option item=item key=key}
						<option value="{$key}"{if $user_type=={$key}} selected{/if}>{$item}</option>
					{/foreach}
				</select>
			</div>
		</div>
		<div class="row">
			<div class="h">{l i='search_by' gid='users'}:</div>
			<div class="v">
				<input type="text" name="val_text" value="{$search_param.text}" class="short_long">
				<select name="type_text" class="ml20 short_long">
					<option value="all" {if $search_param.type=='all'} selected{/if}>{l i='filter_all' gid='users'}</option>
					<option value="email" {if $search_param.type=='email'} selected{/if}>{l i='field_email' gid='users'}</option>
					<option value="fname" {if $search_param.type=='fname'} selected{/if}>{l i='field_fname' gid='users'}</option>
					<option value="sname" {if $search_param.type=='sname'} selected{/if}>{l i='field_sname' gid='users'}</option>
					<option value="nickname" {if $search_param.type=='nickname'} selected{/if}>{l i='field_nickname' gid='users'}</option>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="h">{l i='latest_active' gid='users'}:</div>
			<div class="v middle_long">
				<input type="text" id="last_active_from" name="last_active_from" maxlength="10" class="short_long" value="{$search_param.last_active.from}">
				<label for="last_active_to">{l i='to' gid='users'}</label>
				<input type="text" id="last_active_to" name="last_active_to" maxlength="10" class="short_long fr" value="{$search_param.last_active.to}">
			</div>
		</div>
		<div class="row">
			<div class="btn">
				<div class="l">
					<input type="submit" value="{l i='header_user_find' gid='users'}" name="btn_search">
				</div>
			</div>
		</div>		
	</div>
<table cellspacing="0" cellpadding="0" class="data" width="100%">
<tr>
	<th class="first"><input type="checkbox" id="grouping_all"></th>
	<th><a href="{$sort_links.nickname}"{if $order eq 'nickname'} class="{$order_direction|lower}"{/if}>{l i='field_nickname' gid='users'}</a></th>
	<th>{l i='user_type' gid='users'}</th>
	<th><a href="{$sort_links.email}"{if $order eq 'email'} class="{$order_direction|lower}"{/if}>{l i='field_email' gid='users'}</a></th>
	<th class=""><a href="{$sort_links.date_created}"{if $order eq 'date_created'} class="{$order_direction|lower}"{/if}>{l i='field_date_created' gid='users'}</a></th>
	<th class="w100">&nbsp;</th>
</tr>
{foreach item=item from=$users}
{counter print=false assign='counter'}
<tr class="{if $counter is div by 2}zebra {/if}{if !empty($item.net_is_incomer)}net_incomer{/if}">
	<td class="first w20 center">
		{if !empty($item.net_is_incomer)}
			<div class="corner-triangle" title="{l i='network_is_incomer' gid='users'}"></div>
		{/if}
		<input type="checkbox" name="id_user[]" class="grouping" value="{$item.id}">
	</td>
	<td>
		<b>{$item.nickname}</b><br>{$item.fname} {$item.sname}
	</td>
	<td>{$item.user_type_str}</td>
	<td>{if !empty($item.net_is_incomer)}{l i='network_email' gid='users'}{else}{$item.email}{/if}</td>
	<td class="center">{$item.date_created|date_format:$page_data.date_format}</td>
	<td class="icons">
		<div id="move_block">
			{block name='contact_user_link' module='tickets' id_user=$item.id}
		</div>
	</td>
</tr>
{foreachelse}
<tr><td colspan="7" class="center">{l i='no_users' gid='users'}</td></tr>
{/foreach}
</table>
</form>
{include file="pagination.tpl"}
{js file='jquery-ui.custom.min.js'}
<link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
<script type="text/javascript">

var reload_link = "{$site_url}admin/users/index/";
var filter = '{$filter}';
var order = '{$order}';
var loading_content;
var order_direction = '{$order_direction}';

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

	$('#grouping_all').bind('click', function(){
		var checked = $(this).is(':checked');
		if(checked){
			$('input[type=checkbox].grouping').prop('checked', true);
		}else{
			$('input[type=checkbox].grouping').prop('checked', false);
		}
	});
	now = new Date();
	yr =  (new Date(now.getYear() - 80, 0, 1).getFullYear()) + ':' + (new Date(now.getYear() - 18, 0, 1).getFullYear());
	$( "#last_active_from" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat :'yy-mm-dd',
		onClose: function( selectedDate ) {
			$( "#last_active_to" ).datepicker( "option", "minDate", selectedDate );
		}
    });
    $( "#last_active_to" ).datepicker({
		defaultDate: "+1w",
		changeMonth: true,
		numberOfMonths: 1,
		dateFormat :'yy-mm-dd',
		onClose: function( selectedDate ) {
			$( "#last_active_from" ).datepicker( "option", "maxDate", selectedDate );
		}
    });
		
});
delete_select_block = new loadingContent({
	loadBlockWidth: '620px',
	loadBlockLeftType: 'center',
	loadBlockTopType: 'center',
	loadBlockTopPoint: 100,
	closeBtnClass: 'w'
}).update_css_styles({'z-index': 2000}).update_css_styles({'z-index': 2000}, 'bg');
$('#delete_select_block').unbind('click').click(function(){
	var data = new Array();
	$('.grouping:checked').each(function(i){
		data[i] = $(this).val();
	});
	if(data.length > 0){
		$.ajax({
			url: site_url + 'admin/users/ajax_delete_select/',
			data: {user_ids: data},
			type: "POST",
			cache: false,
			success: function(data){
				delete_select_block.show_load_block(data);
			}
		});
	}else{
		error_object.show_error_block('{/literal}{l i="error_no_users_to_change_group" gid="users" type="js"}{literal}', 'error');
	}
});
function reload_this_page(value){
	var link = reload_link + filter + '/' + value + '/' + order + '/' + order_direction;
	location.href=link;
}
{/literal}</script>

{include file="footer.tpl"}
