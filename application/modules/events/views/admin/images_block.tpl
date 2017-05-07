{foreach item=item_m key=key from=$media}
    {if $item_m.media}
            <span class="store-photo">
                    <img class="pointer" data-click="view-media-photo" data-id-media="{$item_m.id}" src="{$item_m.media.mediafile.thumbs.big}" />
                    <a class="photo-remove" href="{$site_url}admin/events/deleteImage/{$item_m.id}" onclick="javascript: if(!confirm('{l i='note_delete_photo' gid='store' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0"></a>
            </span>
    {elseif $item_m.video_content}
            {if $item_m.info.size eq 'big'}
                    <div class="center-block" style="width:620px; margin:0 auto;">{$item_m.video_content.embed}</div>
                    {if $item_m.info.remove_url}<a class="photo-remove{if $section != 'media'} hide{/if}" href="{$item_m.info.remove_url}" onclick="javascript: if(!confirm('{l i='note_delete_video' gid='store' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0"></a>{/if}
            {else}
                    <span class="store-photo">
                            <img class="pointer" data-click="view-media-video" data-id-media="0" src="{$item_m.video_content.thumbs.big}" />
                            {if $item_m.info.remove_url}<a class="photo-remove{if $section != 'media'} hide{/if}" href="{$item_m.info.remove_url}" onclick="javascript: if(!confirm('{l i='note_delete_video' gid='store' type='js'}')) return false;"><img src="{$site_root}{$img_folder}icon-delete.png" width="16" height="16" border="0"></a>{/if}
                    </span>
            {/if}
    {else}
            <i class="fa fa-gift fa-5x"></i>
    {/if}
{foreachelse}

{/foreach}
