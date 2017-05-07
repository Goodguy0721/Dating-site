{include file="header.tpl"}

{strip}
<div class="content-block kisses">
	<h1>{l i='questions' gid='questions'}</h1>
	<div id="questions_content">
		
	
	{if $questions|@count gt 0}
		<div class="sorter short-line" id="sorter_block">
			<div class="fright">{pagination data=$page_data type='cute'}</div>
		</div>
	{/if}
	<div class="questions-list table-div wp100 list">
		{foreach item=question from=$questions}
			<dl id="question_{$question.id}">				
				<dt class="photo w100" style="vertical-align: top;">
					<a href="{seolink module='users' method='view' data=$question.id_user}" target="_blank">
						<img alt="{$question.user_info}" title="{$question.user_info}" src="{$question.user_logo.small}" /><br/><span>{$question.user_info}</span></a>
				</dt>						
				<dt style="vertical-align: top; width: 60%"><p>{$question.name}</p>
				<br/>
				<a href="{seolink module='users' method='view' data=$question.id_user_to}" class="fleft" target="_blank">
					<img class="w30" alt="{$question.user_info_to}" title="{$question.user_info_to}" src="{$question.user_logo_to.small}" />
                </a>
				{if $question.answer}
					<span id="answer_{$question.id}" class="questions-answer">{$question.answer}</span>
				{else}
					<span id="answer_{$question.id}" class="questions-answer">
                        {if $question.id_user_to == $logged_user_id}
                            <div id="answer_form_{$question.id}">
                                <textarea data-id="{$question.id}" class="js-question-field" maxlength="100" rows="2" 
                                    columns="80" style="box-sizing: border-box; width: 77%; height: 40px; vertical-align: middle;" id="input_answer_{$question.id}" placeholder="{l i='your_answer' gid='questions'}"></textarea>
                                <input type="button" onclick="sendAnswer({$question.id}); return false;" name="btn_save" value="{l i='send' gid='questions' type='button'}" 
                                    id="send_btn_{$question.id}" class="hide" style="box-sizing: border-box; vertical-align: middle; height: 40px;">
                                <div id="answer_symbols_left_{$question.id}"></div>
                                <script type="text/javascript">{literal}
                                    $('#input_answer_{/literal}{$question.id}{literal}').unbind('focus').bind('focus', function() {
                                        $('#send_btn_{/literal}{$question.id}{literal}').show();
                                    });
                                    
                                    $(document.body).on('click', function(event){
                                        if( $(event.target).closest("#send_btn_{/literal}{$question.id}{literal}, 
                                            #input_answer_{/literal}{$question.id}{literal}").length == 0 ) {
                                            $('#send_btn_{/literal}{$question.id}{literal}').hide();
                                        }
                                    });
                                {/literal}</script>
                            </div>
                        {else}
                            {l i='no_answer' gid='questions'}
                        {/if}
                    </span>
				{/if}
                <div class="clr"></div>
							
				<div class="actions noPrint" style="padding-top: 10px;">				
				<div id="action_{$question.id}" class="{if !$question.answer}hide{/if}">				
					{if $question.id_user_to == $logged_user_id}						
						{block name=$helper_func module=$action_module id_user=$question.id_user user_id=$question.id_user id_contact=$question.id_user}
					{else}						
						{block name=$helper_func module=$action_module id_user=$question.id_user_to user_id=$question.id_user_to id_contact=$question.id_user_to}
					{/if}
					{if $action_descr}
					<script type="text/javascript">			
						$('#action_{$question.id}').append('{$action_descr|replace:"'":"\\'"}');						
					</script>
					{/if}
				</div>
                </div>

				</dt>
				<dt class="righted w200">{$question.date_created}</dt>
				<dt class="centered">					
					<a onclick="javascript: if(confirm('{l i='confirm_delete' gid='questions'}')) location.href='{$site_url}questions/delete_question/{$question.id}/{$page}';"><i class='icon-trash icon-big edge hover'></i></a>					
				</dt>
			</dl>
		{foreachelse}
			<div class="line top empty center">{l i='no_questions' gid='questions'}</div>
		{/foreach}
	</div>	
	
	{if $questions|@count gt 0}<div>{pagination data=$page_data type='full'}</div>{/if}
	
	</div>
</div>

<script type="text/javascript">{literal}
    function sendAnswer(question_id){
		$.ajax({
			url: '{/literal}{$site_url}{literal}questions/ajax_save_answer',
			type: 'POST',
			data: {"question_id": question_id, "answer": $('#input_answer_'+question_id).val()},
			dataType: 'json',
			cache: false,			
			success: function(data){
					var errorObj = new Errors;
				if(typeof(data.error) != 'undefined' && data.error != ''){						
					errorObj.show_error_block(data.error, 'error');
				}else{					
					$('#answer_form_' + question_id).hide();
					$('#answer_' + question_id).append( data.answer );
					$('#answer_get_' + question_id).show();
					$('#action_' + question_id).show();
					
					errorObj.show_error_block(data.success, 'success');
				}
			}
		});		
	}
    
    $(function() {
        $('.js-question-field').unbind('keyup').bind('keyup', function(e) {
            var el = $(this);
        
            var question_id = el.data('id');

            if (e.keyCode == 13) {
                if (e.ctrlKey ) {
                    el.val(el.val() + "\n");
                } else {
                    sendAnswer(question_id);
                }
            } 
            
            var maxLength = el.attr('maxlength');
            var curLength = el.val().length;
            el.val(el.val().substr(0, maxLength));
                
            var remaning = maxLength - curLength;
               
            if (remaning < 0) remaning = 0;
                
            var symbols_left = $('#answer_symbols_left_' + question_id);
            symbols_left.html(remaning);
                
            if (remaning < 10) {
                symbols_left.addClass('warning');
            } else {
                symbols_left.removeClass('warning');
            }
        }).each(function() {
            var el = $(this);
        
            var question_id = el.data('id');
            
            var maxLength = el.attr('maxlength');
            var curLength = el.val().length;
            el.val(el.val().substr(0, maxLength));
                
            var symbols_left = $('#answer_symbols_left_' + question_id);
            symbols_left.html(maxLength - curLength);
        });
	});
{/literal}</script>

<div class="clr"></div>
{/strip}

{include file="footer.tpl"}
