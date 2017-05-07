{include file="header.tpl"}
<h1>{seotag tag="header_text"}</h1>

<div class="pos-rel">
    {block name='events_search_form' module='events'}
</div>
<div class="content-block">
	<div id="main_users_results">
		{$block}
	</div>
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