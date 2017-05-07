{strip}
	<div>
		{foreach item=item from=$events}
			{l i='text_user_logo' gid='users' type='button' assign='text_user_logo' replace_array=$item}
			{if $page_data.view_type == 'list'}
				<div>
					<div class="image">
						<a href=""><img src="{$item.image.thumbs.big}" alt="{$text_user_logo}" title="{$text_user_logo}"></a>
					</div>
					<div class="body">
						{l i='no_information' gid='start' assign='no_info_str'}
						<h3>{$item.name}</h3>
						<div class="t-1">
							<span>{l i='field_category' gid='events'}:</span>
                            {foreach item=item_cat key=key_cat from=$category_options.option}{if $item.category eq $key_cat}{$item_cat}{/if}{/foreach}
						</div>						
                        <div class="t-1">
							<span>{l i='field_max_participants' gid='events'}:</span> {$item.max_participants}
						</div>
						<div class="t-2">
							<span>{l i='field_date_started' gid='events'}:</span> {$item.date_started|date_format:$page_data.date_format}
						</div>						
                        <div class="t-2">
							<span>{l i='field_date_ended' gid='events'}:</span> {$item.date_ended|date_format:$page_data.date_format}
						</div>                        
                        <div class="t-2">
							<span>{l i='field_deadline_date' gid='events'}:</span> {$item.deadline_date|date_format:$page_data.date_format}
						</div>
						{if $item.location}
							<div class="t-2">
								<span>{l i='field_location' gid='users'}:</span> {$item.location}
							</div>
						{/if}
					</div>
					<div class="clr"></div>
				</div>
			{/if}
		{foreachelse}
			<div class="item empty">{l i='empty_search_results' gid='users'}</div>
		{/foreach}
	</div>
	{if $users}<div id="pages_block_2">{pagination data=$page_data type='full'}</div>{/if}
{/strip}

<script>{literal}
	$('.user-gallery').not('.w-descr').find('.photo')
		.off('mouseenter').on('mouseenter', function(){
			$(this).find('.info').stop().slideDown(100);
		}).off('mouseleave').on('mouseleave', function(){
			$(this).find('.info').stop(true).delay(100).slideUp(100);
		});
</script>{/literal}
