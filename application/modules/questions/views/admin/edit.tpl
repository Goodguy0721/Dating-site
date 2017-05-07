{include file="header.tpl"}
<form method="post" action="" name="save_form" enctype="multipart/form-data">
	<div class="edit-form n250">
		<div class="row header">{l i='link_edit_question' gid='questions'}</div>
		<div class="row">
            <div class="h">{l i='link_edit_question' gid='questions'}:</div>
            <div class="v">
				{foreach item=lang_item key=lang_id from=$langs}
					{assign var=lang value=$lang_item.id}
					{assign var=name value=$lang_item.id}		
                    {if $lang_id eq $current_lang_id}
					<textarea name="field[{$lang}]" rows="10" columns="80"
						lang-editor="value" lang-editor-type="params-field" 
						lang-editor-lid="{$lang_id}">{$question[$name]|escape}</textarea>
                    {else}
                        <input type="hidden" name="field[{$lang}]" value="{$question[$name]|escape}" 
                            lang-editor="value" lang-editor-type="params-field" lang-editor-lid="{$lang_id}"/>
                    {/if}
				{/foreach}
				<a href="#" lang-editor="button" lang-editor-type="params-field"><img src="{$site_root}{$img_folder}icon-translate.png" width="16" height="16"></a>
			</div>
		</div>
	</div>
	<div class="btn"><div class="l"><input type="submit" name="btn_save" value="{l i='btn_save' gid='start' type='button'}"></div></div>
	<a class="cancel" href="{$back_link}">{l i='btn_cancel' gid='start'}</a>
</form>
{block name='lang_inline_editor' module='start' multiple='1' textarea='1'}

{include file="footer.tpl"}
