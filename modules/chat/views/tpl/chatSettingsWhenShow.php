<section class="lcsMainOptSect">
	<span class="lcsOptLabel"><?php _e('When to show Chat', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_on]', array(
			'value' => 'page_load', 
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on', 'page_load')))?>
		<?php _e('When page loads', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_on_page_load" style="display: none;" class="lcsOptDescParamsShell">
		<label>
			<?php echo htmlLcs::checkbox('engine[params][main][show_on_page_load_enb_delay]', array('checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on_page_load_enb_delay')))?>
			<?php _e('Delay for', LCS_LANG_CODE)?>
		</label>
		<label>
			<?php echo htmlLcs::text('engine[params][main][show_on_page_load_delay]', array(
				'value' => isset($this->chatEngine['params']['main']['show_on_page_load_delay']) ? $this->chatEngine['params']['main']['show_on_page_load_delay'] : '',
				'attrs' => 'style="width: 50px;"',
			));?>
			<span class="supsystic-tooltip" title="<?php _e('Seconds', LCS_LANG_CODE)?>"><?php _e('sec', LCS_LANG_CODE)?></span>
		</label>
	</div><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_on]', array(
			'value' => 'click_on_page',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on', 'click_on_page')))?>
		<?php _e('User click on the page', LCS_LANG_CODE)?>
	</label><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_on]', array(
			'value' => 'click_on_element',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on', 'click_on_element')))?>
		<?php _e('Click on certain link / button / other element', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_on_click_on_element" style="display: none;" class="lcsOptDescParamsShell">
		<?php _e('Copy & paste next code - into required link to open Chat on Click', LCS_LANG_CODE)?>:<br />
		<?php echo htmlLcs::text('lcsCopyTextCode', array(
			'value' => esc_html('['. LCS_SHORTCODE_CLICK. ' id='. $this->chatEngine['id']. ']'),
			'attrs' => 'class="lcsCopyTextCode supsystic-tooltip-right" title="'. esc_html(sprintf(__('Check screenshot with details - <a onclick="lcsShowTipScreenPopUp(this); return false;" href="%s">here</a>.', LCS_LANG_CODE), $this->getModule()->getAssetsUrl(). 'img/show-on-element-click.png')). '"'));?>
		<br />
		<?php _e('Or, if you know HTML basics, - you can insert "onclick" attribute to any of your element from code below', LCS_LANG_CODE)?>:<br />
		<?php echo htmlLcs::text('lcsCopyTextCode', array(
				'value' => esc_html('onclick="'. LCS_JS_FUNC_CLICK. '('. $this->chatEngine['id'] .'); return false;"'),
				'attrs' => 'class="lcsCopyTextCode"'));?><br />
		<?php _e('Or you can even use it for your Menu item, just add code', LCS_LANG_CODE)?>:<br />
		<?php echo htmlLcs::text('lcsCopyTextCode', array(
				'value' => esc_html('#'. LCS_JS_FUNC_CLICK. '_'. $this->chatEngine['id']),
				'attrs' => 'class="lcsCopyTextCode"'));?><br />
		<?php _e('into your menu item "Title Attribute" field. Don\'t worry - users will not see this code as menu item title on your site.', LCS_LANG_CODE)?>
	</div><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_on]', array(
			'value' => 'scroll_window',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on', 'scroll_window')))?>
		<?php _e('Scroll window', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_on_scroll_window" style="display: none;" class="lcsOptDescParamsShell">
		<label>
			<?php echo htmlLcs::checkbox('engine[params][main][show_on_scroll_window_enb_delay]', array('checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on_scroll_window_enb_delay')))?>
			<?php _e('Delay for', LCS_LANG_CODE)?>
		</label>
		<label>
			<?php echo htmlLcs::text('engine[params][main][show_on_scroll_window_delay]', array('value' => isset($this->chatEngine['params']['main']['show_on_scroll_window_delay']) ? $this->chatEngine['params']['main']['show_on_scroll_window_delay'] : 0));?>
			<?php _e('seconds after first scroll', LCS_LANG_CODE)?>
		</label><br />
		<label>
			<?php echo htmlLcs::checkbox('engine[params][main][show_on_scroll_window_enb_perc_scroll]', array('checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on_scroll_window_enb_perc_scroll')))?>
			<?php _e('Scrolled to', LCS_LANG_CODE)?>
		</label>
		<label>
			<?php echo htmlLcs::text('engine[params][main][show_on_scroll_window_perc_scroll]', array('value' => isset($this->chatEngine['params']['main']['show_on_scroll_window_perc_scroll']) ? $this->chatEngine['params']['main']['show_on_scroll_window_perc_scroll'] : 0));?>
			<?php _e('percents of total scroll', LCS_LANG_CODE)?>
		</label>
	</div><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_on]', array(
			'attrs' => 'class="lcsProOpt"',
			'value' => 'on_exit',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_on', 'on_exit')))?>
		<span class="supsystic-tooltip-right" title="<?php echo esc_html(sprintf(__('Show when user tries to exit from your site.', LCS_LANG_CODE), 'http://supsystic.com/exit-live-chat/'))?>">
			<?php _e('On Exit from Site', LCS_LANG_CODE)?>
		</span>
		<?php if(!$this->isPro) {?>
			<span class="lcsProOptMiniLabel"><a target="_blank" href="<?php echo frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium=on_exit&utm_campaign=live_chat');?>"><?php _e('PRO option', LCS_LANG_CODE)?></a></span>
		<?php }?>
	</label><br />
	<label class="lcsMainOptLbl supsystic-tooltip" title="<?php echo esc_html(__('To allow user to send message before agent starts chatting or not', LCS_LANG_CODE))?>">
		<?php echo htmlLcs::checkbox('engine[params][main][wait_agent_response]', array(
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'wait_agent_response')))?>
		<?php _e('Wait for agent response', LCS_LANG_CODE)?>
	</label><br />
</section>
<section class="lcsMainOptSect">
	<span class="lcsOptLabel"><?php _e('Show on next pages', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_pages]', array(
			'value' => 'all',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_pages', 'all')))?>
		<?php _e('All pages', LCS_LANG_CODE)?>
	</label><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_pages]', array(
			'value' => 'show_on_pages',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_pages', 'show_on_pages')))?>
		<?php _e('Show on next pages / posts', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_pages_show_on_pages" style="display: none;" class="lcsOptDescParamsShell">
		<?php echo htmlLcs::selectlist('engine[show_pages_list]', array('options' => $this->allPagesForSelect, 'value' => $this->selectedShowPages, 'attrs' => 'class="chosen chosen-responsive" data-placeholder="'. __('Choose Pages', LCS_LANG_CODE). '"'))?>
	</div><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_pages]', array(
			'value' => 'not_show_on_pages',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_pages', 'not_show_on_pages')))?>
		<?php _e('Don\'t show on next pages / posts', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_pages_not_show_on_pages" style="display: none;" class="lcsOptDescParamsShell">
		<?php echo htmlLcs::selectlist('engine[not_show_pages_list]', array('options' => $this->allPagesForSelect, 'value' => $this->selectedHidePages, 'attrs' => 'class="chosen chosen-responsive" data-placeholder="'. __('Choose Pages', LCS_LANG_CODE). '"'))?>
	</div>
	<br />
	<div style="clear: both;"></div>
	<span class="lcsOptLabel"><?php _e('Time display settings', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::checkbox('engine[params][main][enb_show_time]', array(
			'value' => 'all',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'enb_show_time')))?>
		<?php _e('Set display time', LCS_LANG_CODE)?>
	</label><br />
	<span class="lcsTimeDisplayOptsShell">
		<?php _e('From', LCS_LANG_CODE)?>
		<?php echo htmlLcs::selectbox('engine[params][main][show_time_from]', array(
			'value' => (isset($this->chatEngine['params']['main']['show_time_from']) ? $this->chatEngine['params']['main']['show_time_from'] : ''),
			'attrs' => 'class="time-choosen"',
			'options' => $this->timeRange,
		))?>
		<?php _e('to', LCS_LANG_CODE)?>
		<?php echo htmlLcs::selectbox('engine[params][main][show_time_to]', array(
			'value' => (isset($this->chatEngine['params']['main']['show_time_to']) ? $this->chatEngine['params']['main']['show_time_to'] : ''),
			'attrs' => 'class="time-choosen"',
			'options' => $this->timeRange,
		))?>
	</span>
	<div style="clear: both;"></div>
	<span class="lcsOptLabel"><?php _e('Date display settings', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::checkbox('engine[params][main][enb_show_date]', array(
			'value' => 'all',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'enb_show_date')))?>
		<?php _e('Set display date', LCS_LANG_CODE)?>
	</label><br />
	<span class="lcsDateDisplayOptsShell">
		<?php _e('From', LCS_LANG_CODE)?>
		<?php echo htmlLcs::text('engine[params][main][show_date_from]', array(
			'value' => (isset($this->chatEngine['params']['main']['show_date_from']) ? $this->chatEngine['params']['main']['show_date_from'] : ''),
		))?>
		<?php _e('to', LCS_LANG_CODE)?>
		<?php echo htmlLcs::text('engine[params][main][show_date_to]', array(
			'value' => (isset($this->chatEngine['params']['main']['show_date_to']) ? $this->chatEngine['params']['main']['show_date_to'] : ''),
		))?>
	</span>
	<div style="clear: both;"></div>
	<span class="lcsOptLabel"><?php _e('Days display settings', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::checkbox('engine[params][main][enb_show_days]', array(
			'value' => 'all',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'enb_show_days')))?>
		<?php _e('Set display days', LCS_LANG_CODE)?>
	</label><br />
	<span class="lcsDaysDisplayOptsShell">
		<?php echo htmlLcs::selectlist('engine[params][main][show_days]', array(
			'value' => (isset($this->chatEngine['params']['main']['show_days']) ? $this->chatEngine['params']['main']['show_days'] : false),
			'options' => $this->weekDays, 
			'attrs' => 'class="chosen chosen-responsive" data-placeholder="'. __('Choose week days', LCS_LANG_CODE). '"',
		));?>
	</span>
</section>
<section class="lcsMainOptSect">
	<span class="lcsOptLabel"><?php _e('Whom to show', LCS_LANG_CODE)?></span>
	<hr />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_to]', array(
			'value' => 'everyone',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_to', 'everyone')))?>
		<?php _e('Everyone', LCS_LANG_CODE)?>
	</label><br />
	<label class="lcsMainOptLbl">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_to]', array(
			'value' => 'first_time_visit',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_to', 'first_time_visit')))?>
		<?php _e('For first-time visitors', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_to_first_time_visit" style="display: none;" class="lcsOptDescParamsShell">
		<label class="supsystic-tooltip-left" title="<?php _e('Will remember user visit for entered number of days and show Chat to same user again - after this period. To remember only for one browser session - use 0 here, to remember forever - try to set big number - 99999 for example.')?>">
			<?php _e('Remember for', LCS_LANG_CODE)?>
			<?php echo htmlLcs::text('engine[params][main][show_to_first_time_visit_days]', array(
				'value' => isset($this->chatEngine['params']['main']['show_to_first_time_visit_days']) ? $this->chatEngine['params']['main']['show_to_first_time_visit_days'] : 30,
				'attrs' => 'style="width: 50px;"'
			));?>
			<span><?php _e('days', LCS_LANG_CODE)?></span>
		</label>
	</div><br />
	<label class="supsystic-tooltip-left lcsMainOptLbl" title="<?php _e('Registration, Chat session start, etc.')?>" style="">
		<?php echo htmlLcs::radiobutton('engine[params][main][show_to]', array(
			'value' => 'until_make_action',
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'show_to', 'until_make_action')))?>
		<?php _e('Until user makes an action', LCS_LANG_CODE)?>
	</label>
	<div id="lcsOptDesc_engine_params_main_show_to_until_make_action" style="display: none;" class="lcsOptDescParamsShell">
		<label class="supsystic-tooltip-left" title="<?php _e('Will remember user action for entered number of days and show Chat to same user again - after this period. To remember only for one browser session - use 0 here, to remember forever - try to set big number - 99999 for example.')?>">
			<?php _e('Remember for', LCS_LANG_CODE)?>
			<?php echo htmlLcs::text('engine[params][main][show_to_until_make_action_days]', array(
				'value' => isset($this->chatEngine['params']['main']['show_to_until_make_action_days']) ? $this->chatEngine['params']['main']['show_to_until_make_action_days'] : 30,
				'attrs' => 'style="width: 50px;"'
			));?>
			<span><?php _e('days', LCS_LANG_CODE)?></span>
		</label>
	</div><br />
	<label class="lcsMainOptLbl" id="lcsHideForDevicesLabel">
		<span class="supsystic-tooltip" title="<?php echo esc_html(__('Click to revert feature function: from Hide - to Show, and vice versa.', LCS_LANG_CODE))?>">
			<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="0" data-input-name="engine[params][main][hide_for_devices_show]"><?php _e('Hide', LCS_LANG_CODE)?></a>/<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="1" data-input-name="engine[params][main][hide_for_devices_show]"><?php _e('Show Only', LCS_LANG_CODE)?></a>
			<?php echo htmlLcs::hidden('engine[params][main][hide_for_devices_show]', array(
				'value' => (isset($this->chatEngine['params']['main']['hide_for_devices_show']) ? $this->chatEngine['params']['main']['hide_for_devices_show'] : 0)
			))?>
		</span>
		<?php _e('for Devices', LCS_LANG_CODE)?>
		<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('You can make Chat visible or hidden only when users will view your site from selected devices.', LCS_LANG_CODE))?>"></i>
		:<div style="padding-bottom: 5px; clear: both;"></div>
		<?php echo htmlLcs::selectlist('engine[params][main][hide_for_devices][]', array(
			'options' => $this->hideForList, 
			'value' => (isset($this->chatEngine['params']['main']['hide_for_devices']) ? $this->chatEngine['params']['main']['hide_for_devices'] : array()), 
			'attrs' => 'class="chosen" data-placeholder="'. __('Choose devices', LCS_LANG_CODE). '"'))?>
	</label><br />
	<label class="lcsMainOptLbl lcsMainOptLbl" id="lcsHideForPostTypesLabel">
		<span class="supsystic-tooltip" title="<?php echo esc_html(__('Click to revert feature function: from Hide - to Show, and vice versa.', LCS_LANG_CODE))?>">
			<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="0" data-input-name="engine[params][main][hide_for_post_types_show]"><?php _e('Hide', LCS_LANG_CODE)?></a>/<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="1" data-input-name="engine[params][main][hide_for_post_types_show]"><?php _e('Show Only', LCS_LANG_CODE)?></a>
			<?php echo htmlLcs::hidden('engine[params][main][hide_for_post_types_show]', array(
				'value' => (isset($this->chatEngine['params']['main']['hide_for_post_types_show']) ? $this->chatEngine['params']['main']['hide_for_post_types_show'] : 0)
			))?>
		</span>
		<?php _e('for Post Types', LCS_LANG_CODE)?>
		<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(__('You can make Chat visible or hidden only for specified Post Types, for example - hide it on all Pages.', LCS_LANG_CODE))?>"></i>
		:<div style="padding-bottom: 5px; clear: both;"></div>
		<?php echo htmlLcs::selectlist('engine[params][main][hide_for_post_types][]', array(
			'options' => $this->hideForPostTypesList,
			'value' => (isset($this->chatEngine['params']['main']['hide_for_post_types']) ? $this->chatEngine['params']['main']['hide_for_post_types'] : array()),
			'attrs' => 'class="chosen" data-placeholder="'. __('Choose post types', LCS_LANG_CODE). '"'))?>
	</label><br />
	<label class="lcsMainOptLbl" style="display: inline; vertical-align: middle; padding-top: 12px;">
		<span class="supsystic-tooltip" title="<?php echo esc_html(__('Click to revert feature function: from Hide - to Show, and vice versa.', LCS_LANG_CODE))?>">
			<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="0" data-input-name="engine[params][main][hide_for_ips_show]"><?php _e('Hide', LCS_LANG_CODE)?></a>/<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="1" data-input-name="engine[params][main][hide_for_ips_show]"><?php _e('Show Only', LCS_LANG_CODE)?></a>
			<?php echo htmlLcs::hidden('engine[params][main][hide_for_ips_show]', array(
				'value' => (isset($this->chatEngine['params']['main']['hide_for_ips_show']) ? $this->chatEngine['params']['main']['hide_for_ips_show'] : 0)
			))?>
		</span>
		<?php _e('for IP', LCS_LANG_CODE)?>
		<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(sprintf(__('For those IPs Chat will not be displayed (or vice versa - depending on Hide/Show Only option). Please be advised that your IP - %s', LCS_LANG_CODE), $this->currentIp))?>"></i>
		:<div style="padding-bottom: 5px; clear: both;"></div>
		<a href="#" id="lcsHideForIpBtn" class="button"><?php _e('Show IPs List', LCS_LANG_CODE)?></a><br />
		<?php echo htmlLcs::hidden('engine[params][main][hide_for_ips]', array(
			'value' => (isset($this->chatEngine['params']['main']['hide_for_ips']) ? $this->chatEngine['params']['main']['hide_for_ips'] : '')
		))?>
		<div id="lcsHiddenIpStaticList" class="alert alert-info" style="padding: 5px 0 0; margin: 0;"></div>
	</label><br />
	<label class="lcsMainOptLbl">
		<span class="supsystic-tooltip" title="<?php echo esc_html(__('Click to revert feature function: from Hide - to Show, and vice versa.', LCS_LANG_CODE))?>">
			<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="0" data-input-name="engine[params][main][hide_for_countries_show]"><?php _e('Hide', LCS_LANG_CODE)?></a>/<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="1" data-input-name="engine[params][main][hide_for_countries_show]"><?php _e('Show Only', LCS_LANG_CODE)?></a>
			<?php echo htmlLcs::hidden('engine[params][main][hide_for_countries_show]', array(
				'value' => (isset($this->chatEngine['params']['main']['hide_for_countries_show']) ? $this->chatEngine['params']['main']['hide_for_countries_show'] : 0)
			))?>
		</span>
		<?php _e('for Countries', LCS_LANG_CODE)?>
		<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(sprintf(__('For those Countries Chat will not be displayed (or vice versa - depending on Hide/Show Only option). Please be advised that your Country code is %s', LCS_LANG_CODE), ($this->currentCountryCode ? $this->currentCountryCode : 'undefined (when using localhosts for example)')))?>"></i>
		:<div style="padding-bottom: 5px; clear: both;"></div>
		<?php echo htmlLcs::selectlist('engine[params][main][hide_for_countries][]', array(
			'options' => $this->countriesForSelect, 
			'value' => (isset($this->chatEngine['params']['main']['hide_for_countries']) ? $this->chatEngine['params']['main']['hide_for_countries'] : array()), 
			'attrs' => 'class="chosen chosen-responsive" data-placeholder="'. __('Choose countries', LCS_LANG_CODE). '"'))?>
	</label><br />
	<label class="lcsMainOptLbl">
		<span class="supsystic-tooltip" title="<?php echo esc_html(__('Click to revert feature function: from Hide - to Show, and vice versa.', LCS_LANG_CODE))?>">
			<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="0" data-input-name="engine[params][main][hide_for_languages_show]"><?php _e('Hide', LCS_LANG_CODE)?></a>/<a href="#" class="lcsSwitchShowHideOptLink" data-input-value="1" data-input-name="engine[params][main][hide_for_languages_show]"><?php _e('Show Only', LCS_LANG_CODE)?></a>
			<?php echo htmlLcs::hidden('engine[params][main][hide_for_languages_show]', array(
				'value' => (isset($this->chatEngine['params']['main']['hide_for_languages_show']) ? $this->chatEngine['params']['main']['hide_for_languages_show'] : 0)
			))?>
		</span>
		<?php _e('for Languages', LCS_LANG_CODE)?>
		<i class="fa fa-question supsystic-tooltip" title="<?php echo esc_html(sprintf(__('For those Languages Chat will not be displayed. Language is defined by visitor browser language. Please be advised that your browser language is %s', LCS_LANG_CODE), $this->currentLanguage))?>"></i>
		:<div style="padding-bottom: 5px; clear: both;"></div>
		<?php if(!empty($this->languagesForSelect)) {?>
		<?php echo htmlLcs::selectlist('engine[params][main][hide_for_languages][]', array(
			'options' => $this->languagesForSelect, 
			'value' => (isset($this->chatEngine['params']['main']['hide_for_languages']) ? $this->chatEngine['params']['main']['hide_for_languages'] : array()), 
			'attrs' => 'class="chosen chosen-responsive" data-placeholder="'. __('Choose languages', LCS_LANG_CODE). '"'))?>
		<?php } else { ?>
			<div class="alert alert-danger"><?php _e('This feature is supported only in WordPress version 4.0.0 or higher', LCS_LANG_CODE)?></div>
		<?php }?>
	</label><br />
	<label class="supsystic-tooltip-left lcsMainOptLbl" title="<?php _e('Hide Chat for Logged-in users and show it only for not Logged site visitors.', LCS_LANG_CODE)?>" style="">
		<?php echo htmlLcs::checkbox('engine[params][main][hide_for_logged_in]', array(
			'checked' => htmlLcs::checkedOpt($this->chatEngine['params']['main'], 'hide_for_logged_in')))?>
		<?php _e('Hide for Logged-in', LCS_LANG_CODE)?>
	</label><br />
</section>
<div id="lcsHideForIpWnd" style="display: none;" title="<?php _e('IPs List', LCS_LANG_CODE)?>">
	<label>
		<?php _e('Type here IPs that will not see (or vice versa) Chat, each IP - from new line', LCS_LANG_CODE)?>:<br />
		<?php echo htmlLcs::textarea('hide_for_ips', array(
			'attrs' => 'id="lcsHideForIpTxt" style="width: 100%; height: 300px;"'
		))?>
	</label>
</div>