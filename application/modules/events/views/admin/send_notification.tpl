{include file="header.tpl"}
<div>
	<form method="post" name="save_form" enctype="multipart/form-data" action="{$site_url}admin/events/remindParticipant/{$participant_id}">
		<div class="edit-form n150">
			<div class="row header"></div>
                        <div class="row zebra">
				<div class="h">{l i='field_mail_to_email' gid='notifications'}:</div>
				<div class="v">
					<input type="text" name="email" value="{$email}">
				</div>
			</div>
                        <div class="row zebra">
				<div class="h">{l i='field_subject' gid='notifications'}:</div>
				<div class="v">
					<input type="text" name="subject" value="{$template.subject}">
				</div>
			</div>
			<div class="row zebra">
				<div class="h">{l i='field_content' gid='notifications'}:</div>
				<div class="v">
					<textarea name="content" class="message_box" rows="10">{$template.content}</textarea>
				</div>
			</div>
			<div class="row">
				<div class="btn">
					<div class="l">
						<input type="submit" value="{l i='field_send' gid='events'}" name="btn_save">
					</div>
				</div>
				<a class="cancel" href="{$site_url}admin/events/edit_participants/{$event_id}">{l i='btn_cancel' gid='start'}</a>
			</div>
		</div>
	</form>
</div>
{include file="footer.tpl"}
