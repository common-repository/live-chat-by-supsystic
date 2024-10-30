<table class="form-table" style="width: auto;">
	<tr>
		<th scope="row"><?php _e('Sound Notification', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Sound that will be played for your users and support agents once new message is arrived.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkboxHiddenVal('engine[params][enb_sound]', array(
				'value' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_sound', true, 1),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '"',
			))?>
			<?php echo htmlLcs::audioGalleryBtn('engine[params][sound]', array(
				'value' => (isset($this->chatEngine['params']['sound']) ? $this->chatEngine['params']['sound'] : $this->defSound),
				'btnVal' => __('Select Sound', LCS_LANG_CODE),
				'onChange' => 'lcsOnAdminSoundChange',
				'attrs' => 'class="button"',
			))?>
			<a target="_blank" href="" class="lcsAdminSetDefSoundBtn button"><?php _e('Set default', LCS_LANG_CODE)?></a>
			<br />
			<a target="_blank" href="" class="lcsAdminSoundLink"></a>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Auto update Dashboard', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Periodically auto update Agents Dashboard - to check new chat sessions in auto mode. Once new message will arrive - sound notification will be triggered.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkboxHiddenVal('engine[params][enb_agent_auto_update]', array(
				'value' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_agent_auto_update', true, 1),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '"',
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('New chat start notification', LCS_LANG_CODE)?>
		</th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Enter the email addresses that should receive notifications (separate by comma) about new chat start. Leave it blank - and you will not get any notifications.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][notif_new_email]', array(
				'value' => isset($this->chatEngine['params']['notif_new_email']) 
					? $this->chatEngine['params']['notif_new_email'] 
					: $this->adminEmail,
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('New chat start email text', LCS_LANG_CODE)?>
			<?php $allowVarsInMail = array('sitename', 'siteurl', 'user_data', 'session_data');?>
			<div class="description" style=""><?php printf(__('You can use next variables here: %s', LCS_LANG_CODE), '['. implode('], [', $allowVarsInMail).']')?></div>
		</th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Message that you will receive about new chat start on your site.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::textarea('engine[params][notif_new_message]', array(
				'value' => esc_html(isset($this->chatEngine['params']['notif_new_message']) 
					? $this->chatEngine['params']['notif_new_message'] 
					: __('New chat was started on your site <a href="[siteurl]">[sitename]</a>.<br /><b>User information:</b><br />[user_data]<br /><b>Session information:</b><br />[session_data]<br />', LCS_LANG_CODE)),
			))?>
		</td>
	</tr>
</table>