{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>
<div class="content-block" id="associations">
	<div class="associations">
		{foreach item='item' key='key' from=$associations.list}
			<div class="user-content b-bottom"  id="association_{$item.id}">
			{if $item.user.id != $associations.profile.id}
				<div class="image small minh150">
					<a href="{seolink module='users' method='view' data=$item.user}"><img src="{$item.user.media.user_logo.thumbs.small}" alt="{$item.user.output_name}" title="{$item.user.output_name}" /></a>
					<div class="mb10">
						<a href="{seolink module='users' method='view' data=$item.user}">{$item.user.output_name}</a>, {$item.user.age}
					</div>
				</div>
				<div class="content">
					<i class="icon-caret-left icon-4x fltl"></i>
					<div class="association-block mb10">
						<span class="fleft minh150">
							<img src="{$item.image.thumbs.big}" />
						</span>
                        <span class="ml10 text fright">{$item.name}
                            {if empty($item.answer)}
                            <div class="association-action fright" id="association_action_{$item.id}">
                                <a href="javascript:void(0);" data-answer="cool" data-id="{$item.id}" title="{l i='field_answer_cool' gid='associations'}">
                                    {l i='field_answer_cool' gid='associations'}
                                </a>
                                <a href="javascript:void(0);" data-answer="awesome" data-id="{$item.id}" title="{l i='field_answer_awesome' gid='associations'}">
                                    {l i='field_answer_awesome' gid='associations'}
                                </a>
                                <a href="javascript:void(0);" data-answer="not_like" data-id="{$item.id}" title="{l i='field_answer_not_like' gid='associations'}">
                                    {l i='field_answer_not_like' gid='associations'}
                                </a>
                            </div>	
                            {/if}
                        </span>
					</div>
                    <div class="pl20">
                        {foreach item='set' key='key' from=$settings.chat_more}
                            {if !empty($set.helper)}
                                <span class="pl20" data-name="{$set.name}">
                                    {block name=$set.helper module=$key id_user=$item.user.id user_id=$item.user.id id_contact=$item.user.id}
                                </span>
                            {/if}
                        {/foreach}
                        {$settings.chat_message[$lang_id]}
                    </div>
				</div>
				{if !empty($item.answer)}
				<div class="fright answer">
					<div class="content fleft">
						<i class="icon-caret-right icon-4x fltr"></i>
						<div class="association-block">
							{$item.answer}						
						</div>
					</div>
					<div class="image small fright">
						<img src="{$associations.profile.media.user_logo.thumbs.small}" alt="{$associations.profile.output_name}" title="{$associations.profile.output_name}" />
						<div>{$associations.profile.output_name}, {$associations.profile.age}</div>
					</div>
				</div>
				{/if}
			{else}
				<div class="fright answer">
					<div class="content fleft">
						<i class="icon-caret-right icon-4x fltr"></i>
						<div class="association-block">
                            <span class="mlr10 fright">
								<img src="{$item.image.thumbs.big}" />
							</span>	
							<span class="text fleft">{$item.name}</span>		
						</div>
					</div>
					<div class="image small fright">
						<img src="{$associations.profile.media.user_logo.thumbs.small}" alt="{$associations.profile.output_name}" title="{$associations.profile.output_name}" />
						<div>{$associations.profile.output_name}, {$associations.profile.age}</div>
					</div>
				</div>
				<div class="clr"></div>
                {if !empty($item.answer)}
                    <div class="image small">
                        <a href="{seolink module='users' method='view' data=$item.profile}">
                            <img src="{$item.profile.media.user_logo.thumbs.small}" alt="{$item.profile.output_name}" title="{$item.profile.output_name}" />
                        </a>
                        <div class="mb10">
                            <a href="{seolink module='users' method='view' data=$item.profile}">{$item.profile.output_name}</a>, {$item.profile.age}
                        </div>
                    </div>
                    <div class="content">
                        <i class="icon-caret-left icon-3x fltl"></i>
                        <div class="association-block mt5">{$item.answer}</div>
                        <div class="pl20 pt10">
                            {foreach item='set' key='key' from=$settings.chat_more}
                                {if !empty($set.helper)}
                                    <span class="pl20" data-name="{$set.name}">
                                        {block name=$set.helper module=$key id_user=$item.profile.id user_id=$item.profile.id id_contact=$item.profile.id}
                                    </span>
                                {/if}
                            {/foreach}
                            {$settings.chat_message[$lang_id]}
                        </div>
                    </div>
                {/if}
			{/if}
			</div>
		{foreachelse}
            <div class="center mb10"><h2>{l i='header_send_first' gid='associations'}</h2></div>
            <div>{block name='perfect_match' module='associations'}</div>
            <div class="center mt10 p10"><a href="{$site_url}users/search" class="button">{l i='btn_search' gid='start' type='button'}</a></div>
		{/foreach}
	</div>
	<div class="clr"></div>
	<div id="pages_block_2">{pagination data=$page_data type='full'}</div>
</div>
<script>
{literal}
$(function(){
	loadScripts(
		["{/literal}{js module='associations' file='associations.js' return='path'}{literal}"],
		function(){
			var lang_data = {answer: {cool: "{/literal}{l i='field_answer_cool' gid='associations' type='js'}{literal}", awesome: "{/literal}{l i='field_answer_awesome' gid='associations' type='js'}{literal}", not_like: "{/literal}{l i='field_answer_not_like' gid='associations' type='js'}{literal}"}};
			associations = new Associations({
				siteUrl: site_url,
				lang: lang_data,
				profile: {/literal}{$associations.profile|@json_encode}{literal},
			});
		},
		['associations'],
		{async: true}
	);
});
</script>{/literal}
{include file="footer.tpl"}