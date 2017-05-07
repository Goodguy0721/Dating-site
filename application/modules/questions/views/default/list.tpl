<div class="content-block load_content">
	<h1>{l i='questions_form_title' gid='questions'}</h1>
	<div class="modal-body scroll inside">
		
		
			<form id="questions_form" action="" method="POST" role="form">
				{if $no_questions ne "1"}
				<div>{l i='questions_annotation' gid='questions'}</div>
			
				<ul id="list">
					{foreach from=$questions item=question}
					<li>						
						<label><input type="radio" class="" value="{$question.id}" id="question-{$question.id}" name="question" />
						{$question.name}
						</label>						
					</li>
					{/foreach}					
				</ul>				
				{elseif $no_questions eq "1" && $allow_users_question eq "0"}
					{l i='no_questions' gid='questions'}
				{/if}
		
				{if $allow_users_question eq "1"}
				<ul>					
					<li>
						<div>
						<label>
							<input type="radio" class="" value="0" id="question-0" name="question" {if $no_questions eq "1"}style="display: none;"{/if}/>
							{l i='own_question' gid='questions'}
						</label>
						</div>
                        <textarea onclick="$('#question-0').prop('checked', true)" name="message" id="message" maxlength="100" rows="5" cols="50" style="height: auto;" autocomplete="false"></textarea>
                        <div id="symbols" style="display: inline-block;">{$maxlength}</div>
					</li>					
				</ul>
				{/if}
				<br />
				{if $no_questions ne "1" && $questions_count > 5}
				<div id="btn-refresh" class="btn-questions link-r-margin" title="{l i='refresh' gid='questions'}" style="width: 300px;">
					<i class='fa-refresh icon-big edge hover'></i> {l i='refresh' gid='questions'}
				</div>				
				<br />
				{/if}
				
				{if $no_questions ne "1" || $allow_users_question eq "1"}
                    <input type="button" name="btn_send_questions" value="{l i='send_question' gid='questions'}" id="btn_send_questions" class="btn">
                {/if}
			</form>
		
	</div>
</div>

<script>{literal}
$(function(){
	var maxLength = $('#message').attr('maxlength');
    
    var curLength = $('#message').val().length;
    $(this).val($(this).val().substr(0, maxLength));
        
    var remaning = maxLength - curLength;
    if (remaning < 0) remaning = 0;
        
    $('#symbols').html(remaning);
        
    if (remaning < 10) {
        $('#symbols').addClass('warning');
    } else {
        $('#symbols').removeClass('warning');
    }
    
    $('#message').keyup(function()
    {	
        var curLength = $('#message').val().length;
        $(this).val($(this).val().substr(0, maxLength));
        
        var remaning = maxLength - curLength;
        if (remaning < 0) remaning = 0;
        
        $('#symbols').html(remaning);
        
        if (remaning < 10) {
            $('#symbols').addClass('warning');
        } else {
            $('#symbols').removeClass('warning');
        }
    });
    
});



{/literal}</script>
