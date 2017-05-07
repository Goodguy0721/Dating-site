{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
<div class="menu-level2">
    <ul>
        <li><div class="l"><a id="events_edit_main_item" href="{$site_url}admin/events/edit_main/{$event_id}">{l i='menu_edit_main_item' gid='events'}</a></div></li>
        <li><div class="l"><a id="events_edit_participants_item" href="{$site_url}admin/events/edit_participants/{$event_id}">{l i='menu_edit_participants_item' gid='events'}</a></div></li>
        <li><div class="l"><a id="events_edit_album_item" href="{$site_url}admin/events/edit_album/{$event_id}">{l i='menu_edit_album_item' gid='events'}</a></div></li>
        <li class="active"><div class="l"><a id="events_edit_map_item" href="{$site_url}admin/events/edit_map/{$event_id}">{l i='menu_edit_map_item' gid='events'}</a></div></li>
    </ul>
</div>
<div class="actions">&nbsp;</div>

<script>{literal}
	function get_type_data(type){
		$('#default_view_type option').removeAttr('selected');
		$('#default_view_type option[value='+type+']').attr('selected', 'selected');
	}
	function get_zoom_data(zoom){
		$('#default_zoom').val(zoom);
	}
	function get_drag_data(point_gid, lat, lon){
		$('#default_lat').val(lat);
		$('#default_lon').val(lon);
	}

	$(function(){
		$("#default_lat").bind('keyup', function(){
			map.moveMarker('general', $("#default_lat").val(), $("#default_lon").val());
		});
		$("#default_lon").bind('keyup', function(){
			map.moveMarker('general', $("#default_lat").val(), $("#default_lon").val());
		});
		
		$("#default_zoom").bind('keyup', function(){
			map.setZoom(parseInt($(this).val()));
		});
		$("#default_view_type").bind('change', function(){
			map.setType(parseInt($(this).val())-1);
		});
	});
{/literal}</script>
<form method="post" action="{$data.action}" name="save_form" enctype="multipart/form-data">
	<div class="edit-form n150">
        <input type="hidden" name="lat" id='default_lat' value="{$event.lat|escape}" class="middle">
        <input type="hidden" name="lon" id='default_lon' value="{$event.lon|escape}" class="middle">
		<div class="row">{block name=show_map module=geomap map_gid=$geomap_driver.gid gid=$map_gid markers=$markers settings=$view_settings map_id=map width='738' height='300'}</div>
	</div>
	<div class="btn"><div class="l"><input type="submit" name="save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
</form>

{include file="footer.tpl"}