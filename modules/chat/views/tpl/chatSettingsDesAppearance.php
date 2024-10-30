<table class="form-table" style="width: auto;">
	<tr>
		<th scope="row"><?php _e('Chat Position', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Position of your chat on user screen', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::selectbox('engine[params][chat_position]', array(
				'value' => $this->chatEngine['params']['chat_position'],
				'options' => $this->chatPositions,
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Width', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Width of your chat window', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('tpl[params][width]', array('value' => $this->chatTemplate['params']['width']))?>
			<label style="margin-right: 10px;" class="supsystic-tooltip" title="<?php _e('Max width for percentage - is 100', LCS_LANG_CODE)?>">
				<?php echo htmlLcs::radiobutton('tpl[params][width_measure]', array('value' => '%', 'checked' => $this->chatTemplate['params']['width_measure'] == '%'))?>
				<?php _e('Percents', LCS_LANG_CODE)?>
			</label>
			<label>
				<?php echo htmlLcs::radiobutton('tpl[params][width_measure]', array('value' => 'px', 'checked' => $this->chatTemplate['params']['width_measure'] == 'px'))?>
				<?php _e('Pixels', LCS_LANG_CODE)?>
			</label>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Height of Messages box', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Maximum height for messages box in Chat', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('tpl[params][msg_height]', array(
				'value' => isset($this->chatTemplate['params']['msg_height']) ? $this->chatTemplate['params']['msg_height'] : 300))?>
			<label style="margin-right: 10px;" class="supsystic-tooltip" title="<?php _e('Max width for percentage - is 100', LCS_LANG_CODE)?>">
				<?php echo htmlLcs::radiobutton('tpl[params][msg_height_measure]', array(
					'value' => '%', 
					'checked' => htmlLcs::checkedOpt($this->chatTemplate['params'], 'msg_height_measure', '%')))?>
				<?php _e('Percents', LCS_LANG_CODE)?>
			</label>
			<label>
				<?php echo htmlLcs::radiobutton('tpl[params][msg_height_measure]', array(
					'value' => 'px', 
					'checked' => htmlLcs::checkedOpt($this->chatTemplate['params'], 'msg_height_measure', 'px', true)))?>
				<?php _e('Pixels', LCS_LANG_CODE)?>
			</label>
		</td>
	</tr>
	<tr id="lcsChatBarPaddingRow">
		<th scope="row"><?php _e('Chat Bar padding', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Chat bar offset from the edge of the screen', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::text('engine[params][chat_padding]', array(
				'value' => (isset($this->chatEngine['params']['chat_padding']) ? $this->chatEngine['params']['chat_padding'] : 40),	// 40px by default
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Main Theme Color', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Main color will define main theme design', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::colorpicker('tpl[params][main_color]', array('value' => $this->chatTemplate['params']['main_color']))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Main Theme Titles Color', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Main color for all text titles on your chat', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::colorpicker('tpl[params][main_titles_color]', array('value' => $this->chatTemplate['params']['main_titles_color']))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Main Background Color', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Main background color for your chat window', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::colorpicker('tpl[params][bg_color]', array('value' => $this->chatTemplate['params']['bg_color']))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Main Text Color', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Main color for all text in your chat messages and texts', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::colorpicker('tpl[params][main_txt_color]', array('value' => $this->chatTemplate['params']['main_txt_color']))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Main Font Family', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('You can select font type for your Chat here', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::fontsList('tpl[params][main_font_family]', array(
				'value' => (isset($this->chatTemplate['params']['main_font_family']) ? $this->chatTemplate['params']['main_font_family'] : ''),
				'attrs' => 'id="scsFontFamilySelect" class="chosen"',
				'includeDef' => true,
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Draggable', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Allow users to drag chat screen in any place of browser window', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkbox('engine[params][enb_draggable]', array(
				'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_draggable'),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '"',
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Enable Agent avatar', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Enable agent avatar when this is possible to display in Chat window.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkbox('engine[params][enb_agent_avatar]', array(
				'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_agent_avatar'),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '"',
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row">
			<?php _e('Enable Rating', LCS_LANG_CODE)?>
			<?php if(!$this->isPro) {?>
				<span class="lcsProOptMiniLabel"><a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=agent_rating&utm_campaign=live_chat');?>"><?php _e('PRO option', LCS_LANG_CODE)?></a></span>
			<?php }?>
		</th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(sprintf(__('Enable agent Rating buttons. If enabled - user will be able to rate agent, and you will see - who is best agent from your team. <img src="%s" />', LCS_LANG_CODE), $this->promoModPath. 'img/rating.jpg'))?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkbox('engine[params][enb_rating]', array(
				'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_rating'),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '" class="lcsProOpt"',
			))?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php _e('Enable additonal chat options', LCS_LANG_CODE)?></th>
		<td>
			<i class="fa fa-question supsystic-tooltip" title="<?php _e('Buttons that allow disable sound, or print chat conversation from frontend for example.', LCS_LANG_CODE)?>"></i>
		</td>
		<td>
			<?php echo htmlLcs::checkbox('engine[params][enb_opts]', array(
				'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'enb_opts'),
				'attrs' => 'title="'. __('Enable', LCS_LANG_CODE). '"',
			))?>
		</td>
	</tr>
</table>