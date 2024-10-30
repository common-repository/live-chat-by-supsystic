<table class="form-table" style="width: auto;">
	<?php if(isset($this->chatTemplate['params']['opts_attrs']['have_bar_title']) && $this->chatTemplate['params']['opts_attrs']['have_bar_title']) {?>
	<tr>
		<th scope="row"><?php _e('Chat Bar Title Text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text on your chat bar that user will see before chat opens', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('tpl[params][bar_title]', array('value' => $this->chatTemplate['params']['bar_title']))?>
		</td>
	</tr>
	<?php }?>
	<tr>
		<th scope="row"><?php _e('"Send Message" button text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text for main send button in your chat', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][send_btn_txt]', array('value' => $this->chatEngine['params']['send_btn_txt']))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('"Wait" message', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Message that users will see before operator will start chat', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php htmlLcs::wpEditorWithHidden('engine[params][wait_txt]', array(
				'value' => $this->chatEngine['params']['wait_txt'],
			));?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Chat header text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('You can add some text to the header of your main chat section', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php htmlLcs::wpEditorWithHidden('engine[params][chat_header_txt]', array(
				'value' => (isset($this->chatEngine['params']['chat_header_txt']) ? $this->chatEngine['params']['chat_header_txt'] : ''),
			));?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Complete chat text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text that users will see after chat will be completed', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php htmlLcs::wpEditorWithHidden('engine[params][complete_txt]', array(
				'value' => (isset($this->chatEngine['params']['complete_txt']) ? $this->chatEngine['params']['complete_txt'] : ''),
			));?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('"Operator joined chat" message', LCS_LANG_CODE)?>
			<span class="description"><?php _e('Use [name] tag in this field, it will be replaced to operator name in chat session', LCS_LANG_CODE)?></span>
		</th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text that will show when operator is joined user chat', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][chat_agent_joined_txt]', array(
				'value' => (isset($this->chatEngine['params']['chat_agent_joined_txt']) ? $this->chatEngine['params']['chat_agent_joined_txt'] : ''),
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Default text for message field', LCS_LANG_CODE)?>
		</th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('This text will be placed in message text (placeholder) to indicate where exactly user should start print his message', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][msg_placeholder]', array(
				'value' => (isset($this->chatEngine['params']['msg_placeholder']) ? $this->chatEngine['params']['msg_placeholder'] : __('Ask me here...', LCS_LANG_CODE)),
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('"Register" button text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text that will appear on your registration button', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][register_btn_txt]', array(
				'value' => $this->chatEngine['params']['register_btn_txt'],
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Before registration message text', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Text that will appear on registration form if registration is enabled', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php htmlLcs::wpEditorWithHidden('engine[params][before_reg_txt]', array(
				'value' => $this->chatEngine['params']['before_reg_txt'],
			));?>
		</td>
	</tr>
</table>