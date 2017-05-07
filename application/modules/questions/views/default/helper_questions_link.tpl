<div class="user-menu-item">
<a id="btn-questions-{$user_id}" class="btn-questions link-r-margin"
   title="{l i='question' gid='questions' type='button'}"
   href="javascript:void(0);">
	{l i='question' gid='questions' type='button'}
</a>
</div>

<script>{literal}
	$(function(){
		
		loadScripts(
			"{/literal}{js file='questions_form.js' module='questions' return='path'}{literal}",
			function(){
				questions = new Questions({
					siteUrl: site_url,
					use_form: true,
					btnForm: '{/literal}btn-questions-{$user_id}{literal}',
					btnRefresh: '{/literal}btn-refresh{literal}',
					urlGetForm: '{/literal}questions/ajax_get_questions/{$user_id}{literal}',
					urlSendForm: '{/literal}questions/ajax_set_questions/{$user_id}{literal}',
					urlGetData: '{/literal}questions/ajax_refresh_questions/{$user_id}{literal}',
					dataType: '{/literal}{if $is_user}html{else}json{/if}{literal}',
					compared: '{/literal}{$user_compared}{literal}',
					error_sent: '{/literal}{l i='error_sent' gid='questions'}{literal}'
				});
			},
			['questions'],
			{async: false}
		);
		
	});
{/literal}</script>
