<label class="lcsRegistrationTypeLbl supsystic-tooltip" title="<?php _e('Fully disable registration for your chat', LCS_LANG_CODE)?>">
	<?php echo htmlLcs::radiobutton('engine[params][reg_type]', array(
		'value' => 'none',
		'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'reg_type', 'none'),
	))?>
	<?php _e('No Registration', LCS_LANG_CODE)?>
</label>
<label class="lcsRegistrationTypeLbl supsystic-tooltip" title="<?php _e('Registration will be enabled, but not required', LCS_LANG_CODE)?>">
	<?php echo htmlLcs::radiobutton('engine[params][reg_type]', array(
		'value' => 'ask',
		'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'reg_type', 'ask'),
	))?>
	<?php _e('Ask for Registration', LCS_LANG_CODE)?>
</label>
<label class="lcsRegistrationTypeLbl supsystic-tooltip" title="<?php _e('Registration will required: user will not be able to start chat without it', LCS_LANG_CODE)?>">
	<?php echo htmlLcs::radiobutton('engine[params][reg_type]', array(
		'value' => 'required',
		'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'reg_type', 'required'),
	))?>
	<?php _e('Required', LCS_LANG_CODE)?>
</label>
<div id="lcsChatSettingsRegistrationShell">
	<table class="form-table" style="width: auto;">
		<?php /*?><tr>
			<th scope="row"><?php _e('When to Register', LCS_LANG_CODE)?></th>
			<td>
				<i class="fa fa-question supsystic-tooltip" title="<?php _e('You can enable rgistration not only in the beginning of your chat session, but also ask it after chat will start', LCS_LANG_CODE)?>"></i>
			</td>
			<td>
				<label>
					<?php echo htmlLcs::radiobutton('engine[params][reg_event]', array(
						'value' => 'before_chat',
						'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'reg_event', 'before_chat'),
					))?>
					<?php _e('Before Chat', LCS_LANG_CODE)?>
				</label>
				<label>
					<?php echo htmlLcs::radiobutton('engine[params][reg_event]', array(
						'value' => 'after_first_msg',
						'checked' => htmlLcs::checkedOpt($this->chatEngine['params'], 'reg_event', 'after_first_msg'),
					))?>
					<?php _e('After User First Message', LCS_LANG_CODE)?>
				</label>
			</td>
		</tr><?php */?>
		<tr>
			<th scope="row"><?php _e('Registration Fields', LCS_LANG_CODE)?>:</th>
			<td>
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('To change field position - just drag-&-drop it to required place between other fields. To add new field to Subscribe form - click on "+ Add" button.', LCS_LANG_CODE))?>"></i>
			</td>
			<td>
				<a href="#" id="lcsRegAddFieldBtn" class="button button-primary">
					<i class="fa fa-plus"></i>
					<?php _e('Add', LCS_LANG_CODE)?>
				</a>
				<?php if(!$this->isPro) {?>
					<span class="lcsProOptMiniLabel" style="margin-bottom: 0; margin-top: -5px;">
						<a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=reg_fields&utm_campaign=live_chat');?>"><?php _e('PRO option', LCS_LANG_CODE)?></a>
					</span>
				<?php }?>
			</td>
		</tr>
		<tr>
			<td colspan="3">
				<table id="lcsRegFieldsTbl" class="form-table">
					<tr id="lcsRegExRow" class="lcsRegFieldRow">
						<td class="lcsMoveHandle">
							<i class="fa fa fa-arrows-v"></i>
						</td>
						<td>
							<?php echo htmlLcs::checkbox('engine[params][reg_fields][][enb]', array(
								'value' => 1,
							))?>
						</td>
						<th scope="row">
							<?php echo htmlLcs::text('engine[params][reg_fields][][label]')?>
							<?php echo htmlLcs::hidden('engine[params][reg_fields][][html]')?>
							<?php echo htmlLcs::hidden('engine[params][reg_fields][][mandatory]')?>
							<?php echo htmlLcs::hidden('engine[params][reg_fields][][name]')?>
						</th>
						<td>
							<a href="#" class="button" onclick="_lcsShowRegFieldSettingsWndClk(this); return false;">
								<i class="fa fa-fw fa-pencil"></i>
								<?php _e('Edit', LCS_LANG_CODE)?>
							</a>
							<a href="#" class="button" onclick="_lcsRemoveRegFieldClk(this); return false;">
								<i class="fa fa-fw fa-close"></i>
								<?php _e('Remove', LCS_LANG_CODE)?>
							</a>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
</div>
<div id="lcsRegFieldSettingsWnd" title="<?php _e('Field Settings', LCS_LANG_CODE)?>" style="display: none;">
	<table class="form-table">
		<tr>
			<th scope="row">
				<?php _e('Name', LCS_LANG_CODE)?>*
				<?php //TODO: Add correct link in field description here?>
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(sprintf(__('Name (key) for your field. This parameter is for system - to be able to determine the field. Use here only latin letters, numbers, symbols -_+ and space. For more info about this parameter - your can check <a href="%s" target="_blank">this page</a>.', LCS_LANG_CODE), 'http://supsystic.com/'))?>"></i>
			</th>
			<td>
				<?php echo htmlLcs::text('name')?>
			</td>
		</tr>
		<tr class="lcsSfLabelShell">
			<th scope="row">
				<?php _e('Label', LCS_LANG_CODE)?>*
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Label that will be visible for your users.', LCS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlLcs::text('label')?>
			</td>
		</tr>
		<tr class="lcsSfValueShell">
			<th scope="row">
				<?php _e('Value', LCS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Default value for your field', LCS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlLcs::text('value')?>
			</td>
		</tr>
		<tr class="lcsSfValueShell">
			<th scope="row">
				<?php _e('HTML Type', LCS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Input HTML type', LCS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlLcs::selectbox('html', array(
					'options' => $this->availableHtmlTypes,
					'value' => 'text',
				))?>
				<?php if(!$this->isPro) {?>
					<span class="lcsProOptMiniLabel" style="margin-bottom: 0; margin-top: -5px;">
						<a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=reg_fields&utm_campaign=live_chat');?>"><?php _e('PRO option', LCS_LANG_CODE)?></a>
					</span>
				<?php }?>
			</td>
		</tr>
		<tr class="lcsRegFieldSelectOptsRow" style="display: none;">
			<th colspan="2">
				<?php _e('Select Options', LCS_LANG_CODE)?>
				<a class="button button-small lcsRegFieldSelectOptAddBtn">
					<i class="fa fa-plus"></i>
				</a>
			</th>
		</tr>
		<tr class="lcsRegFieldSelectOptsRow" style="display: none; height: auto;">
			<td colspan="2" style="padding: 0;">
				<div id="lcsRegFieldSelectOptsShell">
					<div id="lcsRegFieldSelectOptShellExl" class="lcsRegFieldSelectOptShell">
						<i class="fa fa-arrows-v lcsMoveHandle"></i>
						<?php echo htmlLcs::text('options[][name]', array(
							'placeholder' => __('Name', LCS_LANG_CODE),
							'disabled' => true,
						))?>
						<?php echo htmlLcs::text('options[][label]', array(
							'placeholder' => __('Label', LCS_LANG_CODE),
							'disabled' => true,
						))?>
						<a href="#" class="button button-small lcsRegFieldSelectOptRemoveBtn" title="<?php _e('Remove', LCS_LANG_CODE)?>">
							<i class="fa fa-trash-o"></i>
						</a>
					</div>
				</div>
			</td>
		</tr>
		<tr>
			<th scope="row">
				<?php _e('Mandatory', LCS_LANG_CODE)?>
				<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('Is this field mandatory to fill-in. If yes - then users will not be able to continue without filling-in this field.', LCS_LANG_CODE))?>"></i>
			</th>
			<td>
				<?php echo htmlLcs::checkbox('mandatory', array(
					'value' => 1,
				))?>
			</td>
		</tr>
	</table>
</div>
<!--Add Field promo Wnd-->
<div id="lcsRegAddFieldWnd" title="<?php _e('Registration Field Settings', LCS_LANG_CODE)?>" style="display: none;">
	<a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=reg_fields&utm_campaign=live_chat');?>" class="lcsPromoImgUrl">
		<img src="<?php echo $this->promoModPath?>img/reg-fields-edit.jpg" />
	</a>
</div>