{strip}
<form action="{$form_settings.action}" method="POST" id="main_search_form_{$form_settings.form_id}">
    <div class="search-form {$form_settings.type}">
        <div class="inside">
            <div class="btn-block">
                <div class="search-btn">
                    <button type="submit" id="main_search_button_{$form_settings.form_id}" name="search_button">{l i='button_search' gid='events' type='button'}</button>
                </div>
            </div>
            <div class="fields-block aligned-fields">
                <div id="short-search-form_{$form_settings.form_id}">
                    <div class="search-field">
                        {selectbox input='category' id='category' value=$category_options.option selected=$data.category default=$all_select_lang}                         
                    </div>
                    <div class="search-field">
                        <div>
                            <span class="inline vmiddle">{l i='field_date_started' gid='events'}&nbsp;{l i='from' gid='events'}&nbsp;</span>
                            <div class="ib vmiddle"><input type='text' value='{$$data.date_started_from}' name="date_started_from" id="datepicker_date_started" maxlength="10" class="middle" style="width:85px;"></div>
                            {js file='jquery-ui.custom.min.js'}
                            <link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
                            <script>{literal}
                                $(function(){
                                    now = new Date();
                                    $( "#datepicker_date_started" ).datepicker({
                                        dateFormat :'yy-mm-dd',
                                        changeYear: true,
                                        changeMonth: true
                                    });
                                });
                            {/literal}</script>                                
                            <span class="inline vmiddle">&nbsp;{l i='to' gid='events'}&nbsp;</span>
                            <div class="ib vmiddle"><input type='text' value='{$$data.date_started_to}' name="date_started_to" id="datepicker_date_ended" maxlength="10" class="middle" style="width:85px;"></div>
                            {js file='jquery-ui.custom.min.js'}
                            <link href='{$site_root}{$js_folder}jquery-ui/jquery-ui.custom.css' rel='stylesheet' type='text/css' media='screen' />
                            <script>{literal}
                                $(function(){
                                    now = new Date();
                                    $( "#datepicker_date_ended" ).datepicker({
                                        dateFormat :'yy-mm-dd',
                                        changeYear: true,
                                        changeMonth: true
                                    });
                                });
                            {/literal}</script>                                
                        </div>
                    </div>
                </div>
                <div class="clr"></div>
            </div>
        </div>
    </div>
</form>
{/strip}
