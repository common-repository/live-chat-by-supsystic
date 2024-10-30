<section>
	<a class="button lcsChatTriggersAddBtn" href="#">
		<span class="lcsChatTriggersAddBtnTxt">
			<i class="fa fa-fw fa-plus"></i><?php _e('Add Trigger', LCS_LANG_CODE)?>
		</span>
		<span class="lcsChatTriggersCancellBtnTxt">
			<i class="fa fa-fw fa-close"></i><?php _e('Cancell', LCS_LANG_CODE)?>
		</span>
	</a>
	<span class="lcsChatTriggersForm" style="display: none;">
		<label>
			<?php _e('Trigger Name', LCS_LANG_CODE)?>
			<?php echo htmlLcs::text('trigger[label]')?>
		</label>
		<a href="#" class="button button-primary lcsChatTriggersSaveBtn">
			<i class="fa fa-save"></i>
			<?php _e('Save', LCS_LANG_CODE)?>
		</a>
		<div class="lcsTriggersEditFormTitle"><?php _e('Conditions', LCS_LANG_CODE)?>:</div>
		<table class="lcsChatTriggersConditions form-table">
			<tr class="lcsChatTriggersConditionRow lcsChatTriggersConditionExRow">
				<td class="lcsMoveHandle">
					<i class="fa fa-arrows-alt"></i>
				</td>
				<td>
					<?php echo htmlLcs::selectbox('trigger[conditions][0][type]', array(
						'options' => $this->chatTriggersConditionsTypes,
						'attrs' => 'disabled="disabled" class="lcsTriggersConditionsTypeSel" onchange="lcsTriggersConditionChangeTypeClk(this, event);"',
					))?>
					<?php if(!$this->isPro) {?>
					<?php /*It should not be visible here - it need only for lcsProOptChangedClb() call*/ ?>
					<span class="lcsProOptMiniLabel" style="margin-bottom: 0; margin-top: -5px; display: none;">
						<a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=chat_triggers&utm_campaign=live_chat');?>"><?php _e('PRO option', LCS_LANG_CODE)?></a>
					</span>
				<?php }?>
				</td>
				<td>
					<?php echo htmlLcs::selectbox('trigger[conditions][0][equal]', array(
						'options' => $this->chatTriggersConditionsEquals,
						'attrs' => 'disabled="disabled" class="lcsTriggersConditionsEqualSel"',
					))?>
				</td>
				<td class="lcsTriggerConditionValCell">
					<?php echo htmlLcs::text('trigger[conditions][0][value]', array(
						'attrs' => 'disabled="disabled"',
					))?>
				</td>
				<td>
					<a class="button lcsChatTriggersAddCondition" onclick="lcsTriggersConditionAddClk(this); return false;" href="#"><i class="fa fa-plus"></i></a>
					<a class="button lcsChatTriggersRemoveCondition" onclick="lcsTriggersConditionRemoveClk(this); return false;" href="#"><i class="fa fa-minus"></i></a>
				</td>
			</tr>
		</table>
		<div class="lcsTriggersEditFormTitle"><?php _e('Actions', LCS_LANG_CODE)?>:</div>
		<div class="lcsChatTriggersActions">
			<?php foreach($this->chatTriggersActions as $aId => $a) { ?>
			<div class="lcsChatTriggerAction">
				<label>
					<?php echo htmlLcs::checkbox('trigger[actions]['. $aId. '][enb]', array(
						'value' => 1,
						'attrs' => 'class="lcsChatTriggerActionCheck" data-code="'. $a['code']. '"',
					))?>
					<?php echo htmlLcs::hidden('trigger[actions]['. $aId. '][id]', array('value' => $aId))?>
					<?php echo htmlLcs::hidden('trigger[actions]['. $aId. '][code]', array('value' => $a['code']))?>
					<?php echo $a['label']?>
					<i class="fa fa-level-down" id="lcsTriggerToActionDataArrow_<?php echo $a['code'];?>" style="display: none;"></i>
				</label>
				<div style="clear: both;"></div>
				<?php switch($a['code']) {
					case LCS_SHOW_EAE_CATCH: ?>
						<div id="lcsChatTriggerAction_<?php echo LCS_SHOW_EAE_CATCH;?>" class="lcsChatTriggerActionDataShell">
							<?php echo htmlLcs::imgGalleryBtn('trigger[actions]['. $aId. '][eye_img]', array(
								'onChange' => 'lcsTriggerSetEyeCatchImgClk',
								'attrs' => 'class="button"',
							))?><br />
							<img class="lcsCahtTriggerEyeCachImg" src="" /><br />
							<table>
								<tr>
									<td>
										<?php $inpFieldId = 'lcsTrigger_actions_'. $aId. '_anim_key';?>
										<label for="<?php echo $inpFieldId;?>"><?php _e('Animation type', LCS_LANG_CODE)?></label>
									</td>
									<td>
										<?php echo htmlLcs::selectbox('trigger[actions]['. $aId. '][anim_key]', array(
											'value' => 'bounce_down',
											'options' => $this->animationsForSelect,
											'attrs' => 'id="'. $inpFieldId. '"'
										))?>
									</td>
								</tr>
								<tr>
									<td>
										<?php $inpFieldId = 'lcsTrigger_actions_'. $aId. '_anim_speed';?>
										<label for="<?php echo $inpFieldId;?>"><?php _e('Animation speed', LCS_LANG_CODE)?></label>
									</td>
									<td>
										<?php echo htmlLcs::text('trigger[actions]['. $aId. '][anim_speed]', array(
											'value' => '1000',
											'attrs' => 'class="lcsTriggersActionAnimSpeedTxt" id="'. $inpFieldId. '"'
										))?>
										<?php _e('ms', LCS_LANG_CODE)?>
									</td>
								</tr>
							</table>
						</div>
				<?php break;
					case LCS_AUTO_START: ?>
						<div id="lcsChatTriggerAction_<?php echo LCS_AUTO_START;?>" class="lcsChatTriggerActionDataShell">
							<label>
								<?php _e('Default message for auto-opened chat', LCS_LANG_CODE)?>:
								<?php echo htmlLcs::text('trigger[actions]['. $aId. '][msg]')?>
							</label>
						</div>
					<?php break;
				}?>
			</div>
			<?php }?>
			<div style="clear: both;"></div>
		</div>
		<?php echo htmlLcs::hidden('trigger[id]')?>
		<?php echo htmlLcs::hidden('trigger[engine_id]', array('value' => $this->chatEngine['id']))?>
		<?php echo htmlLcs::hidden('trigger[active]', array('value' => 1))	// Always enabled for now?>
	</span>
	<hr />
	<table id="lcsChatTriggersTbl"></table>
	<div id="lcsChatTriggersTblEmptyMsg" style="display: none;">
		<p>
			<?php echo __('You have no triggers for now. You can simply click <a href="#" class="lcsChatTriggersAddBtn">here</a> to add new one, or on button above.')?>
		</p>
	</div>
</section>
<div style="display: none;">
	<div id="lcsTriggerCondValue_txt" class="lcsTriggerCondValue">
		<?php echo htmlLcs::text('trigger[conditions][0][value]')?>
	</div>
	<div id="lcsTriggerCondValue_pages_posts" class="lcsTriggerCondValue">
		<?php echo htmlLcs::selectlist('trigger[conditions][0][value]', array('options' => $this->allPagesForSelect, 'attrs' => 'class="chosen-dynamic" data-placeholder="'. __('Choose Pages', LCS_LANG_CODE). '"'))?>
	</div>
	<div id="lcsTriggerCondValue_country" class="lcsTriggerCondValue">
		<?php echo htmlLcs::selectlist('trigger[conditions][0][value]', array('options' => $this->countriesForSelect, 'attrs' => 'class="chosen-dynamic" data-placeholder="'. __('Choose countries', LCS_LANG_CODE). '"'))?>
	</div>
	<div id="lcsTriggerCondValue_hour" class="lcsTriggerCondValue">
		<?php echo htmlLcs::number('trigger[conditions][0][value]', array('min' => 0, 'max' => 23))?>
	</div>
	<div id="lcsTriggerCondValue_week_day" class="lcsTriggerCondValue">
		<?php echo htmlLcs::selectlist('trigger[conditions][0][value]', array('options' => $this->weekDays, 'attrs' => 'class="chosen-dynamic" data-placeholder="'. __('Choose week days', LCS_LANG_CODE). '"'))?>
	</div>
	<div id="lcsTriggerCondValue_time_on_page" class="lcsTriggerCondValue">
		<label>
			<?php echo htmlLcs::number('trigger[conditions][0][value][min]', array('value' => 0, 'min' => 0))?>
			<?php _e('Min', LCS_LANG_CODE)?>
		</label>
		<label>
			<?php echo htmlLcs::number('trigger[conditions][0][value][sec]', array('value' => 0, 'min' => 0))?>
			<?php _e('Sec', LCS_LANG_CODE)?>
		</label>
	</div>
</div>