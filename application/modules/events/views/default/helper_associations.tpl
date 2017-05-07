<a id="btn-association-{$associations_button_rand}" class="link-r-margin"
   title="{l i='link_send' gid='associations' type='button'}"
   href="javascript:void(0);">
	<i class='fa-random icon-big edge hover {if $associations.compared}g{/if}'></i>
</a>
<script>
{literal}
$(function(){
	loadScripts(
		["{/literal}{js module='associations' file='associations.js' return='path'}{literal}"],
		function(){
			var lang_data = {compare: "{/literal}{l i='button_compare' gid='associations' type='js'}{literal}", more: "{/literal}{l i='button_more' gid='associations' type='js'}{literal}", already_sent: "{/literal}{l i='error_already_sent' gid='associations' type='js'}{literal}", associations_empty: "{/literal}{l i='error_associations_empty' gid='associations' type='js'}{literal}"};
			associations = new Associations({
				siteUrl: site_url,
				profile_id: '{/literal}{$associations.profile_id}{literal}',
				btn_id: '{/literal}btn-association-{$associations_button_rand}{literal}',
				compared: '{/literal}{$associations.compared}{literal}',
				lang: lang_data,
			});
		},
		['associations'],
		{async: true}
	);
});
</script>{/literal}