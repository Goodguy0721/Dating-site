{include file="header.tpl"}
<div class="menu-level2">
    <ul>
        <li class="active"><div class="l"><a id="events_edit_main_item" href="{$site_url}admin/events/edit_main/{$event_id}">{l i='menu_edit_main_item' gid='events'}</a></div></li>
        <li><div class="l"><a id="events_edit_participants_item" href="{$site_url}admin/events/edit_participants/{$event_id}">{l i='menu_edit_participants_item' gid='events'}</a></div></li>
        <li><div class="l"><a id="events_edit_album_item" href="{$site_url}admin/events/media_list/{$event_id}">{l i='menu_edit_album_item' gid='events'}</a></div></li>
    </ul>
</div>
<form method="post" name="save_form" enctype="multipart/form-data">
    <div class="edit-form n200">
        <div class="row header">{l i='admin_header_settings' gid='events'}</div>		
		<div class="row">
                <div class="h">{l i='field_name' gid='events'}:&nbsp;*</div>
            <div class="v">
				{foreach item=lang_item key=lang_id from=$langs}
					{assign var='name' value='name_'+$lang_id}
					<input type="{if $lang_id eq $current_lang_id}text{else}hidden{/if}" name="name_{$lang_id}" value="{$event[$name]|escape}" lang-editor="value" lang-editor-type="data-name" lang-editor-lid="{$lang_id}" />
				{/foreach}
				<a href="#" lang-editor="button" lang-editor-type="data-name"><img src="{$site_root}{$img_folder}icon-translate.png" width="16" height="16"></a>
				<div>{l i='field_clarification' gid='events'}</div>
			</div>
        </div>
        <div class="row">
            <div class="h">{ld_header i='category' gid='events'}:&nbsp;*</div>
            <div class="v">
				{ld gid='events' i='category'}
				<select name="category" class="middle">
					{foreach item=item key=key from=$ld_category.option}
						<option value="{$key}" {if $event.category eq $key}selected{/if}>{$item}</option>
					{/foreach}
				</select>
			</div>
        </div>       
        <div class="row">
            <div class="h">{l i='field_description' gid='events'}:&nbsp;*</div>
            <div class="v">
                {foreach item=lang_item key=lang_id from=$langs}
                {assign var='description' value='description_'+$lang_id}
                {if $lang_id eq $current_lang_id}
                <textarea name="description_{$lang_id}" rows="10" cols="80" class="long" 
                    lang-editor="value" lang-editor-type="data-description" 
                    lang-editor-lid="{$lang_id}">{$event[$description]|escape}</textarea>
                {else}
                <input type="hidden" name="description_{$lang_id}" value="{$event[$description]|escape}" 
                    lang-editor="value" lang-editor-type="data-description" lang-editor-lid="{$lang_id}">
                {/if}
                {/foreach}
                <a href="#" lang-editor="button" lang-editor-type="data-description" lang-field-type="textarea">
                    <img src="{$site_root}{$img_folder}icon-translate.png" width="16" height="16">
                </a>
            </div> 
        </div>          
        <div class="row zebra">
            <div class="h">{l i='field_location' gid='events'}:&nbsp;*</div>
            <div class="v">
                {block name='location_select' module='countries' select_type='city' id_country=$event.country_code id_region=$event.fk_region_id id_city=$event.fk_city_id}
			</div>
        </div>
        <div class="row">
            <div class="h">{l i='field_address' gid='events'}:&nbsp;*</div>
            <div class="v">
				<input type="text" name="address" class="middle" value="{$event.address}">
			</div>
        </div>          
        <div class="row zebra">
            <div class="h">{l i='field_venue' gid='events'}:&nbsp;*</div>
            <div class="v">
				<input type="text" name="venue" class="middle" value="{$event.venue}">
			</div>
        </div>              
        <div class="row">
            <div class="h">{l i='field_date_started' gid='events'}:&nbsp;*</div>
            <div class="v">
                <input type='text' value='{$event.date_started}' name="date_started" id="datepicker_date_started" maxlength="10" class="middle">
                <input type="time" value='{$event.time_started}' name="time_started" placeholder="00:00">
                <input type='hidden' value='{$event.date_started}' name="alt_date_started" id="alt_date_started">
            </div>
            {js file='jquery-ui.custom.min.js'}
            <link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
            <script>{literal}
                $(function(){
                    now = new Date();
                    $( "#datepicker_date_started" ).datepicker({
                        dateFormat :'yy-mm-dd',
                        altFormat: 'yy-mm-dd',
                        altField: '#alt_date_started',
                        changeYear: true,
                        changeMonth: true
                    });
                });
            {/literal}</script>
        </div>    
        <div class="row zebra">
            <div class="h">{l i='field_date_ended' gid='events'}:&nbsp;*</div>
            <div class="v">
                <input type='text' value='{$event.date_ended}' name="date_ended" id="datepicker_date_ended" maxlength="10" class="middle">
                <input type="time" value='{$event.time_ended}' name="time_ended" placeholder="00:00">
                <input type='hidden' value='{$event.date_ended}' name="alt_date_ended" id="alt_date_ended">
            </div>
            {js file='jquery-ui.custom.min.js'}
            <link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
            <script>{literal}
                $(function(){
                    now = new Date();
                    $( "#datepicker_date_ended" ).datepicker({
                        dateFormat :'yy-mm-dd',
                        altFormat: 'yy-mm-dd',
                        altField: '#alt_date_ended',
                        changeYear: true,
                        changeMonth: true
                    });
                });
            {/literal}</script>
        </div>         
        <div class="row">
            <div class="h">{l i='field_deadline_date' gid='events'}: </div>
            <div class="v">
                <input type='text' value='{$event.deadline_date}' name="deadline_date" id="datepicker_deadline_date" maxlength="10" class="middle">
                <input type='time' value='{$event.deadline_time}' name="deadline_time" placeholder="00:00">
                <input type='hidden' value='{$event.deadline_date}' name="alt_deadline_date" id="alt_deadline_date">
            </div>
            {js file='jquery-ui.custom.min.js'}
            <link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
            <script>{literal}
                $(function(){
                    now = new Date();
                    $( "#datepicker_deadline_date" ).datepicker({
                        dateFormat :'yy-mm-dd',
                        altFormat: 'yy-mm-dd',
                        altField: '#alt_deadline_date',
                        changeYear: true,
                        changeMonth: true
                    });
                });
            {/literal}</script>
        </div>          
        <div class="row zebra">
            <div class="h">{l i='field_img' gid='events'}:</div>
			<div class="v">
				<input type="file" name="{$upload_gid}">
				{if $event.image}
                                <br><input type="checkbox" name="event_icon_delete" value="1" id="uichb"><label for="uichb">{l i='field_icon_delete' gid='users'}</label><br>
				<image src="{$event.image.thumbs.big}" title="{$event.name}"  alt="{$event.name}">
				{/if}
			</div>            
        </div>
        <div class="row">
            <div class="h">{l i='field_max_participants' gid='events'}:</div>
            <div class="v">
                <input type="text" name="max_participants" placeholder="{l i='text_unlimited_participants' gid='events'}" class="middle" value="{$event.max_participants}">
			</div>
        </div>   
        
        
        
        
        <div class="row">
            <div class="h">{l i='field_event_settings' gid='events'}:</div>
        </div>     
        <div class="row">
            <div class="h">{l i='field_is_invite_other' gid='events'}:</div>
            <div class="v">
                        <input type="checkbox" name="event_settings[is_user_invite]" { if $event.settings.is_user_invite }checked="checked"{/if}>
			</div>
        </div>     
        <div class="row">
            <div class="h">{l i='field_is_upload_media' gid='events'}:</div>
            <div class="v">
                        <input type="checkbox" name="event_settings[is_upload_media]" { if $event.settings.is_upload_media }checked="checked"{/if}>
			</div>
        </div>     
        <div class="row">
            <div class="h">{l i='field_is_user_can_join' gid='events'}:</div>
            <div class="v">
                        <input type="checkbox" name="event_settings[is_user_can_join]" { if $event.settings.is_user_can_join }checked="checked"{/if}>
			</div>
        </div>     
        
    </div>
    <div class="btn">
        <div class="l">
            <input type="hidden" name="save" value="{l i='btn_save' gid='start' type='button'}">
            <input type="button" onclick="$('[name=save_form]').submit();" name="save" value="{l i='btn_save' gid='start' type='button'}">
        </div>
    </div>
</form>
<div class="clr"></div>
{block name='lang_inline_editor' module='start' input='1'}
{include file="footer.tpl"}