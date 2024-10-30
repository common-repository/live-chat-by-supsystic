<?php
class chatViewLcs extends viewLcs {
	protected $_twig;
	private $_animationList = array();
	public function getSettingsTabContent() {
		global $wpdb;
		/*$optsMod = frameLcs::_()->getModule('options');
		$optsModel = $optsMod->getModel();*/
		
		$this->assign('isPro', frameLcs::_()->getModule('supsystic_promo')->isPro());
		
		frameLcs::_()->getModule('templates')->loadJqGrid();
		frameLcs::_()->getModule('templates')->loadJqueryUi();
		frameLcs::_()->getModule('templates')->loadSortable();
		frameLcs::_()->getModule('templates')->loadCodemirror();
		frameLcs::_()->getModule('templates')->loadBootstrapPartial();
		frameLcs::_()->getModule('templates')->loadDatePicker();

		// Current engine
		$chatEngine = $this->getModel()->getCurrentEngine();
		$chatEngine['triggers'] = $this->getModel('chat_triggers')->getBy('engine_id', $chatEngine['id']);
		// Original chat templates
		$chatTemplates = $this->getModel('chat_templates')->getSimpleTplsList(array('original_id' => 0));
		$chatTemplate = $this->getModel('chat_templates')->getForEngine($chatEngine['id']);
		/*COMMENT THIS - Template Creation Kit*/
		//var_dump($chatTemplate);
		//$chatTemplate['params']['bar_title'] = 'Let\'s chat?';
		//$chatTemplate['params']['opts_attrs']['have_bar_title'] = 1;
		//$chatEngine['params'] = $this->getModel('chat_engines')->getDefaultParams();
		/*****/
		
		$chatPositions = $this->getChatPositions();
		
		$previewUrl = uriLcs::mod('chat', 'getPreviewHtml');
		frameLcs::_()->addStyle('admin.chat', $this->getModule()->getModPath(). 'css/admin.chat.css');
		
		frameLcs::_()->addScript('wp.tabs', LCS_JS_PATH. 'wp.tabs.js');
		dispatcherLcs::addAction('afterAdminBreadcrumbs', array($this, 'showSettingsTopFormControls'));
		dispatcherLcs::addAction('adminBreadcrumbsClassAdd', array($this, 'adminBreadcrumbsClassAdd'));
		
		$defSound = $this->getModule()->getModPath(). 'files/vogel.wav';
		
		frameLcs::_()->addStyle('admin.chat', $this->getModule()->getModPath(). 'css/admin.chat.css');
		frameLcs::_()->addScript('admin.chat', $this->getModule()->getModPath(). 'js/admin.chat.js');
		frameLcs::_()->addJSVar('admin.chat', 'lcsChatTemplate', $chatTemplate);
		frameLcs::_()->addJSVar('admin.chat', 'lcsChatEngine', $chatEngine);
		frameLcs::_()->addJSVar('admin.chat', 'lcsEngineTxtEditors', $this->getModel()->getEngineTxtEditors());
		frameLcs::_()->addJSVar('admin.chat', 'lcsDefaultSound', $defSound);
		
		$bgType = array(
			'none' => __('None', LCS_LANG_CODE),
			'img' => __('Image', LCS_LANG_CODE),
			'color' => __('Color', LCS_LANG_CODE),
		);
		
		$hideForList = array(
			'mobile' => __('Mobile', LCS_LANG_CODE),
			'tablet' => __('Tablet', LCS_LANG_CODE),
			'desktop' => __('Desktop PC', LCS_LANG_CODE),
		);
		
		$postTypes = get_post_types('', 'objects');
		$hideForPostTypesList = array();
		foreach($postTypes as $key => $value) {
			if(!in_array($key, array('attachment', 'revision', 'nav_menu_item'))) {
				$hideForPostTypesList[$key] = $value->labels->name;
			}
		}
		
		// We are not using wp methods here - as list can be very large - and it can take too much memory
		$allPages = dbLcs::get("SELECT ID, post_title FROM $wpdb->posts WHERE post_type IN ('page', 'post') AND post_status IN ('publish','draft') ORDER BY post_title");
		$allPagesForSelect = array();
		if(!empty($allPages)) {
			foreach($allPages as $p) {
				$allPagesForSelect[ $p['ID'] ] = $p['post_title'];
			}
		}
		$allPagesForSelect[ LCS_HOME_PAGE_ID ] = __('Main Home page', LCS_LANG_CODE);
		
		$selectedShowPages = array();
		$selectedHidePages = array();
		if(isset($chatEngine['show_pages_list']) && !empty($chatEngine['show_pages_list'])) {
			foreach($chatEngine['show_pages_list'] as $showPage) {
				if($showPage['not_show']) {
					$selectedHidePages[] = $showPage['post_id'];
				} else {
					$selectedShowPages[] = $showPage['post_id'];
				}
			}
		}
		$currentIp = utilsLcs::getIP();
		$currentCountryCode = $this->getModule()->getCountryCode();
		$currentLanguageCode = utilsLcs::getBrowserLangCode();
		$currentLanguage = '';
		
		$allCountries = frameLcs::_()->getTable('countries')->get('*');
		$countriesForSelect = array();
		foreach($allCountries as $c) {
			$countriesForSelect[ $c['iso_code_2'] ] = $c['name'];
		}
		$languagesForSelect = array();
		$allLanguages = array();
		if(!function_exists('wp_get_available_translations') && file_exists(ABSPATH . 'wp-admin/includes/translation-install.php')) {
			require_once( ABSPATH . 'wp-admin/includes/translation-install.php' );
		}
		if(function_exists('wp_get_available_translations')) {	// As it was included only from version 4.0.0
			$allLanguages = wp_get_available_translations();
			if(!empty($allLanguages)) {
				foreach($allLanguages as $l) {
					if(!isset($l['iso']) || !isset($l['iso'][1])) continue;
					$languagesForSelect[ $l['iso'][1] ] = $l['native_name'];
					if($currentLanguageCode == $l['iso'][1]) {
						$currentLanguage = $l['native_name'];
					}
				}
			}
		}
		
		// Time selects
		$this->assign('timeRange', utilsLcs::getTimeRange());
		//$this->assign('engineTxtEditors', $this->getModel()->getEngineTxtEditors());
		$this->assign('chatPositions', $chatPositions);
		$this->assign('chatEngine', $chatEngine);
		$this->assign('previewUrl', $previewUrl);
		$this->assign('chatTemplate', $chatTemplate);
		/*$this->assign('optsModel', $optsModel);
		$this->assign('optsMod', $optsMod);*/
		$this->assign('bgTypes', $bgType);
		$this->assign('hideForList', $hideForList);
		$this->assign('hideForPostTypesList', $hideForPostTypesList);
		$this->assign('allPagesForSelect', $allPagesForSelect);
		$this->assign('selectedShowPages', $selectedShowPages);
		$this->assign('selectedHidePages', $selectedHidePages);
		$this->assign('countriesForSelect', $countriesForSelect);
		$this->assign('languagesForSelect', $languagesForSelect);
		$this->assign('currentIp', $currentIp);
		$this->assign('currentCountryCode', $currentCountryCode);
		$this->assign('currentLanguage', $currentLanguage);
		//$this->assign('closeBtns', $this->getCloseBtns());
		$this->assign('chatTemplates', $chatTemplates);
		
		$this->assign('adminEmail', get_bloginfo('admin_email'));
		
		$this->assign('mainLink', frameLcs::_()->getModule('supsystic_promo')->getMainLink());
		$this->assign('promoModPath', frameLcs::_()->getModule('supsystic_promo')->getAssetsUrl());
		$this->assign('defSound', $defSound);
		
		$this->assign('weekDays', utilsLcs::getWeekDaysArray());
		
		$designTabs = array(
			'lcsChatApppearance' => array(
				'title' => __('Appearance', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsDesAppearance(),
				'fa_icon' => 'fa-picture-o',
				'sort_order' => 0),
			'lcsChatTexts' => array(
				'title' => __('Texts', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsDesTexts(),
				'fa_icon' => 'fa-pencil-square-o',
				'sort_order' => 10),
		);
		$this->assign('designTabs', $designTabs);
		$tabs = array(
			'lcsChatDesign' => array(
				'title' => __('Design', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsDesign(),
				'fa_icon' => 'fa-tachometer',
				'sort_order' => 0),
			'lcsWhenShow' => array(
				'title' => __('When show', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsWhenShow(),
				'fa_icon' => 'fa-eye',
				'sort_order' => 10),
			'lcsTriggers' => array(
				'title' => __('Pre-Chats Triggers', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsTriggers(),
				'fa_icon' => 'fa-cog',
				'sort_order' => 20),
			'lcsRegistration' => array(
				'title' => __('User Registration', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsRegistration(),
				'fa_icon' => 'fa-user',
				'sort_order' => 30),
			'lcsNotifications' => array(
				'title' => __('Notifications', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsNotifications(),
				'fa_icon' => 'fa-bell-o',
				'sort_order' => 35),
			'lcsAgents' => array(
				'title' => __('Agents', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsAgents(),
				'fa_icon' => 'fa-user-md',
				'sort_order' => 40),
			'lcsCodeEditor' => array(
				'title' => __('CSS / HTML Code', LCS_LANG_CODE), 
				'content' => $this->getChatSettingsCode(),
				'fa_icon' => 'fa-code',
				'sort_order' => 999),
		);

		$tabs = dispatcherLcs::applyFilters('chatSettingsTabs', $tabs);
		uasort($tabs, array($this, 'sortSettingsTabsClb'));

		dispatcherLcs::doAction('beforeChatEditRender', $chatEngine);
		$this->assign('tabs', $tabs);
		
		return parent::getContent('chatSettingsAdmin');
	}
	public function adminBreadcrumbsClassAdd() {
		echo ' supsystic-sticky';
	}
	public function showSettingsTopFormControls() {
		parent::display('chatSettingsTopFormControls');
	}
	public function sortSettingsTabsClb($a, $b) {
		if($a['sort_order'] > $b['sort_order'])
			return 1;
		if($a['sort_order'] < $b['sort_order'])
			return -1;
		return 0;
	}
	public function getChatSettingsDesAppearance() {
		return parent::getContent('chatSettingsDesAppearance');
	}
	public function getChatSettingsDesTexts() {
		return parent::getContent('chatSettingsDesTexts');
	}
	public function getChatSettingsDesign() {
		return parent::getContent('chatSettingsDesign');
	}
	public function getChatSettingsWhenShow() {
		return parent::getContent('chatSettingsWhenShow');
	}
	public function getChatSettingsTriggers() {
		$conditionTypes = $this->getModel('chat_triggers')->getTypes();
		if(!$this->isPro) {
			foreach($conditionTypes as $id => $t) {
				if(isset($t['is_pro']) && $t['is_pro']) {
					$conditionTypes[ $id ]['label'] .= ' - PRO';
					$conditionTypes[ $id ]['code'] .= '-pro';
				}
			}
		}
		$conditionTypesForSelect = array();
		foreach($conditionTypes as $id => $c) {
			$conditionTypesForSelect[ $id ] = $c['label'];
		}
		$conditionEquals = $this->getModel('chat_triggers')->getEquals();
		$conditionEqualsForSelect = array();
		foreach($conditionEquals as $id => $c) {
			$conditionEqualsForSelect[ $id ] = $c['label'];
		}
		$triggerActions = $this->getModel('chat_triggers')->getActions();
		
		$animationsForSelect = array('none' => __('None', LCS_LANG_CODE));
		$this->getAnimationList();
		foreach($this->_animationList as $k => $a) {
			$animationsForSelect[ $k ] = $a['label'];
		}
		
		frameLcs::_()->addScript('admin.chat.triggers', $this->getModule()->getModPath(). 'js/admin.chat.triggers.js');
		frameLcs::_()->addJSVar('admin.chat.triggers', 'lcsConditionTypes', $conditionTypes);
		frameLcs::_()->addJSVar('admin.chat.triggers', 'lcsConditionEquals', $conditionEquals);
		
		$this->assign('chatTriggersConditionsTypes', $conditionTypesForSelect);
		$this->assign('chatTriggersConditionsEquals', $conditionEqualsForSelect);
		$this->assign('chatTriggersActions', $triggerActions);
		$this->assign('animationsForSelect', $animationsForSelect);
		return parent::getContent('chatSettingsTriggers');
	}
	public function getChatSettingsRegistration() {
		frameLcs::_()->addScript('lcs.admin.registration', frameLcs::_()->getModule('registration')->getModPath(). 'js/admin.registration.js');
		$availableHtmlTypes = array(
			'text' => __('Text', LCS_LANG_CODE),
			'textarea' => __('Text area', LCS_LANG_CODE),
			'selectbox' => __('Selectbox', LCS_LANG_CODE),
			'checkbox' => __('Checkbox', LCS_LANG_CODE),
			'hidden' => __('Hidden Field', LCS_LANG_CODE),
		);
		if(!$this->isPro) {
			$newHtmlTypes = array();
			foreach($availableHtmlTypes as $k => $hT) {
				if(in_array($k, array('text'))) {
					$newHtmlTypes[ $k ] = $hT;
				} else {
					$newHtmlTypes[ $k. '-pro' ] = $hT. ' - PRO';
				}
			}
			$availableHtmlTypes = $newHtmlTypes;
		}
		$this->assign('availableHtmlTypes', $availableHtmlTypes);
		return parent::getContent('chatSettingsRegistration');
	}
	public function getChatSettingsNotifications() {
		return parent::getContent('chatSettingsNotifications');
	}
	public function getChatSettingsAgents() {
		frameLcs::_()->addScript('admin.chat.agents', $this->getModule()->getModPath(). 'js/admin.chat.agents.js');
		frameLcs::_()->addJSVar('admin.chat.agents', 'lcsChatAgentsListTblUrl', uriLcs::mod('chat', 'getListForTbl', array('reqType' => 'ajax', '_model' => 'chat_users', 'user_types' => 'agents')));
		$this->assign('addNewAgentUrl', admin_url('user-new.php?lcs_agent=1'));
		return parent::getContent('chatSettingsAgents');
	}
	public function getChatSettingsCode() {
		return parent::getContent('chatSettingsCode');
	}
	public function getAnimationList() {
		if(empty($this->_animationList)) {
			$this->_animationList = array_merge($this->_animationList, array(
				'bounce' => array('label' => __('Bounce', LCS_LANG_CODE), 'show_class' => 'bounceIn', 'hide_class' => 'bounceOut'),
				'bounce_up' => array('label' => __('Bounce Up', LCS_LANG_CODE), 'show_class' => 'bounceInUp', 'hide_class' => 'bounceOutUp'),
				'bounce_down' => array('label' => __('Bounce Down', LCS_LANG_CODE), 'show_class' => 'bounceInDown', 'hide_class' => 'bounceOutDown'),
				'bounce_left' => array('label' => __('Bounce Left', LCS_LANG_CODE), 'show_class' => 'bounceInLeft', 'hide_class' => 'bounceOutLeft'),
				'bounce_right' => array('label' => __('Bounce Right', LCS_LANG_CODE), 'show_class' => 'bounceInRight', 'hide_class' => 'bounceOutRight'),
				
				'fade' => array('label' => __('Fade', LCS_LANG_CODE), 'show_class' => 'fadeIn', 'hide_class' => 'fadeOut'),
				'fade_up' => array('label' => __('Fade Up', LCS_LANG_CODE), 'show_class' => 'fadeInUp', 'hide_class' => 'fadeOutUp'),
				'fade_down' => array('label' => __('Fade Down', LCS_LANG_CODE), 'show_class' => 'fadeInDown', 'hide_class' => 'fadeOutDown'),
				'fade_left' => array('label' => __('Fade Left', LCS_LANG_CODE), 'show_class' => 'fadeInLeft', 'hide_class' => 'fadeOutLeft'),
				'fade_right' => array('label' => __('Fade Right', LCS_LANG_CODE), 'show_class' => 'fadeInRight', 'hide_class' => 'fadeOutRight'),
				
				'flip_x' => array('label' => __('Flip X', LCS_LANG_CODE), 'show_class' => 'flipInX', 'hide_class' => 'flipOutX'),
				'flip_y' => array('label' => __('Flip Y', LCS_LANG_CODE), 'show_class' => 'flipInY', 'hide_class' => 'flipOutY'),

				'rotate' => array('label' => __('Rotate', LCS_LANG_CODE), 'show_class' => 'rotateIn', 'hide_class' => 'rotateOut'),
				'rotate_up_left' => array('label' => __('Rotate Up Left', LCS_LANG_CODE), 'show_class' => 'rotateInUpLeft', 'hide_class' => 'rotateOutUpLeft'),
				'rotate_up_right' => array('label' => __('Rotate Up Right', LCS_LANG_CODE), 'show_class' => 'rotateInUpRight', 'hide_class' => 'rotateOutUpRight'),
				'rotate_down_left' => array('label' => __('Rotate Down Left', LCS_LANG_CODE), 'show_class' => 'rotateInDownLeft', 'hide_class' => 'rotateOutDownLeft'),
				'rotate_down_right' => array('label' => __('Rotate Down Right', LCS_LANG_CODE), 'show_class' => 'rotateInDownRight', 'hide_class' => 'rotateOutDownRight'),
				
				'slide_up' => array('label' => __('Slide Up', LCS_LANG_CODE), 'show_class' => 'slideInUp', 'hide_class' => 'slideOutUp'),
				'slide_down' => array('label' => __('Slide Down', LCS_LANG_CODE), 'show_class' => 'slideInDown', 'hide_class' => 'slideOutDown'),
				'slide_left' => array('label' => __('Slide Left', LCS_LANG_CODE), 'show_class' => 'slideInLeft', 'hide_class' => 'slideOutLeft'),
				'slide_right' => array('label' => __('Slide Right', LCS_LANG_CODE), 'show_class' => 'slideInRight', 'hide_class' => 'slideOutRight'),
				
				'zoom' => array('label' => __('Zoom', LCS_LANG_CODE), 'show_class' => 'zoomIn', 'hide_class' => 'zoomOut'),
				'zoom_up' => array('label' => __('Zoom Up', LCS_LANG_CODE), 'show_class' => 'zoomInUp', 'hide_class' => 'zoomOutUp'),
				'zoom_down' => array('label' => __('Zoom Down', LCS_LANG_CODE), 'show_class' => 'zoomInDown', 'hide_class' => 'zoomOutDown'),
				'zoom_left' => array('label' => __('Zoom Left', LCS_LANG_CODE), 'show_class' => 'zoomInLeft', 'hide_class' => 'zoomOutLeft'),
				'zoom_right' => array('label' => __('Zoom Right', LCS_LANG_CODE), 'show_class' => 'zoomInRight', 'hide_class' => 'zoomOutRight'),
				
				'light_speed' => array('label' => __('Light Speed', LCS_LANG_CODE), 'show_class' => 'lightSpeedIn', 'hide_class' => 'lightSpeedOut'),
				'roll' => array('label' => __('Rolling!', LCS_LANG_CODE), 'show_class' => 'rollIn', 'hide_class' => 'rollOut'),
			));
		}
		return $this->_animationList;
	}
	public function getAnimationByKey($key) {
		$this->getAnimationList();
		return isset($this->_animationList[ $key ]) ? $this->_animationList[ $key ] : false;
	}
	public function adjustBrightness($hex, $steps) {
		 // Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));
		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex, 0, 1), 2). str_repeat(substr($hex, 1, 1), 2). str_repeat(substr($hex, 2, 1), 2);
		}
		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0, min(255, $color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
	public function adjustOpacity($color, $opacity) {
		// Steps should be between 0 and 1
		$opacity = max(0, min(1, $opacity));
		if(strpos($color, '#') !== false) {
			$color = utilsLcs::hexToRgb($color);
		} else {
			$color = utilsLcs::rgbToArray($color);
		}
		$color[ 3 ] = $opacity;
		return utilsLcs::rgbToStr( $color );
	}
	public function generateInputFormStart( $chatEngine ) {
		return '<form class="lcsInputForm" action="'. LCS_SITE_URL. '" method="post">';
	}
	public function generateInputFormEnd( $chatEngine ) {
		$res = '';
		$res .= htmlLcs::hidden('mod', array('value' => 'chat'));
		$res .= htmlLcs::hidden('action', array('value' => 'userSend'));
		$res .= htmlLcs::hidden('id', array('value' => $chatEngine['id']));
		$res .= htmlLcs::hidden('_wpnonce', array('value' => wp_create_nonce('msg-'. $chatEngine['id'])));
		$res .= '<div class="lcsSendMsg"></div>';
		$res .= '</form>';
		return $res;
	}
	public function generateHtml($chatTemplate, $params = array()) {
		$replaceStyleTag = isset($params['replace_style_tag']) ? $params['replace_style_tag'] : false;
		if(is_numeric($chatTemplate)) {
			$chatTemplate = $this->getModel('chat_templates')->getById($chatTemplate);
		}
		$chatEngine = $this->getModel('chat_engines')->getById( $chatTemplate['engine_id'] );
		$this->_initTwig();

		$chatTemplate['params']['registration_form_start'] = frameLcs::_()->getModule('registration')->generateFormStart( $chatEngine );
		$chatTemplate['params']['user_fields'] = frameLcs::_()->getModule('registration')->generateUserFields( $chatEngine );
		$chatTemplate['params']['registration_form_end'] = frameLcs::_()->getModule('registration')->generateFormEnd( $chatEngine );
		
		$chatTemplate['params']['input_form_start'] = $this->generateInputFormStart( $chatEngine );
		$chatTemplate['params']['input_form_end'] = $this->generateInputFormEnd( $chatEngine );
		
		$engieToTplReassign = array_merge(array('send_btn_txt', 'register_btn_txt', 'chat_agent_joined_txt', 'chat_header_txt', 'complete_txt', 'msg_placeholder', 'def_avatar', 'enb_agent_avatar', 'enb_rating', 'enb_opts'), $this->getModel()->getEngineTxtEditors());
		foreach($engieToTplReassign as $k) {
			if(isset($chatEngine['params'][ $k ])) {
				$chatTemplate['params'][ $k ] = $chatEngine['params'][ $k ];
			} else {
				$chatTemplate['params'][ $k ] = '';
			}
		}
		// For now - some values don't need to present in options, but already need to be as variables - so put them here
		$hardcodedItems = array(
			'btn_print_txt' => __('Print', LCS_LANG_CODE),
			'btn_send_to_email_txt' => __('Send to email', LCS_LANG_CODE),
			'btn_switch_sound' => __('Switch sound', LCS_LANG_CODE),
		);
		foreach($hardcodedItems as $k => $v) {
			if(!isset($chatTemplate['params'][ $k ])) {
				$chatTemplate['params'][ $k ] = $v;
			}
		}
		if(isset($chatTemplate['params']['main_font_family']) && !empty($chatTemplate['params']['main_font_family'])) {
			$chatTemplate['css'] .= '#[HID] { font-family: '. $chatTemplate['params']['main_font_family']. '; }';
			$chatTemplate['html'] .= '<link class="lclFont" rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family='. urlencode($chatTemplate['params']['main_font_family']). '" />';
		}
		if(isset($chatTemplate['params']['msg_height']) && !empty($chatTemplate['params']['msg_height'])) {
			$heightMeasure = $chatTemplate['params']['msg_height_measure'];
			if($heightMeasure == '%')
				$heightMeasure = 'vh';	// let's compute it from viewport height - "vh"
			$height = $heightMeasure == 'px' ? (int) $chatTemplate['params']['msg_height'] : (float) $chatTemplate['params']['msg_height'];
			$chatTemplate['css'] .= '#[HID] .lcsMessagesShell { max-height: '. $height. $heightMeasure. '; overflow: auto; }';
		}
		// TODO: Create contact form functionality, empty for now - as it present in Templates for futher compatibility
		$chatTemplate['params']['contact_form_content'] = '';
		$chatTemplate['css'] = $this->_replaceTagsWithTwig( $chatTemplate['css'], $chatTemplate );
		$chatTemplate['html'] = $this->_replaceTagsWithTwig( $chatTemplate['html'], $chatTemplate );
		
		$chatTemplate['css'] = dispatcherLcs::applyFilters('chatCss', $chatTemplate['css'], $chatTemplate);
		$chatTemplate['html'] = dispatcherLcs::applyFilters('chatHtml', $chatTemplate['html'], $chatTemplate);
		
		$session = $this->getModule()->getSession();
		$dataParamsArr = array(
			'session_id' => $session && !in_array($session['status_code'], array(LCS_SES_COMPLETE, LCS_SES_CANCELLED)) 
				? $session['id'] : 0,
		);
		$dataParamsStr = '';
		foreach($dataParamsArr as $k => $v) {
			$dataParamsStr .= ' data-'. $k. '="'. $v. '"';
		}
		// $replaceStyleTag can be used for compability with other plugins minify functionality: 
		// it will not recognize css in js data as style whye rendering on server side, 
		// but will be replaced back to normal <style> tag in JS, @see js/frontend.chat.js
		return $this->_twig->render(
				($replaceStyleTag ? '<span style="display: none;" id="lcsChatStylesHidden_'. $chatTemplate['view_id']. '">' : '<style type="text/css">')
					. $chatTemplate['css']
				. ($replaceStyleTag ? '</span>' : '</style>')
				. '<div id="'. $chatTemplate['view_html_id']. '" class="lcsShell" '. $dataParamsStr. '>'
					. $chatTemplate['html']
				. '</div>'
				. $this->_generateEmailInput( $chatTemplate['view_html_id'] ),
			array('chat' => $chatTemplate)
		);
	}
	private function _generateEmailInput( $viewHtmlId ) {
		return '<div id="'. $viewHtmlId. '_emailInputOverlay" class="lcsEmailInputOverlay" style="display: none;">'
		. '<div class="lcsEmailInputShell"><form>'
			. htmlLcs::email('email', array('placeholder' => __('Enter your email here', LCS_LANG_CODE)))
			. '<button name="save" class="button button-primary"><i class="fa fa-fw fa-save"></i>'. __('Save', LCS_LANG_CODE). '</button>'
			. '<a href="#" class="button">'. __('Cancell', LCS_LANG_CODE). '</a>'
		. '</form></div>'
		. '</div>';
	}
	private function _replaceTagsWithTwig($string, $chatTemplate) {
		$string = preg_replace('/\[if (.+)\]/iU', '{% if chat.params.$1 %}', $string);
		$string = preg_replace('/\[elseif (.+)\]/iU', '{% elseif chat.params.$1 %}', $string);		
		
		$replaceFrom = array('ID', 'HID', 'endif', 'else');
		$replaceTo = array($chatTemplate['view_id'], $chatTemplate['view_html_id'], '{% endif %}', '{% else %}');
		// Standard shortcode processor didn't worked for us here - as it is developed for posts, 
		// not for direct "do_shortcode" call, so we created own embed shortcode processor
		add_shortcode('embed', array($this, 'processEmbedCode'));
		if(isset($chatTemplate['params']) && !empty($chatTemplate['params'])) {
			foreach($chatTemplate['params'] as $key => $val) {
				if(is_array($val)) {
					foreach($val as $key2 => $val2) {
						if(is_array($val2)) {
							foreach($val2 as $key3 => $val3) {
								// Here should be some recursive and not 3 circles, but have not time for this right now, maybe you will do this?:)
								if(is_array($val3)) continue;
								$replaceFrom[] = $key. '_'. $key2. '_'. $key3;
								$replaceTo[] = $val3;
							}
						} else {
							$replaceFrom[] = $key. '_'. $key2;
							$replaceTo[] = $val2;
						}
					}
				} else {
					// Do shortcodes for all text type data in chat
					if(strpos($key, 'txt_') === 0 || strpos($key, 'label') === 0 || strpos($key, 'foot_note')) {
						$val = do_shortcode( $val );
					}
					$replaceFrom[] = $key;
					$replaceTo[] = $val;
				}
			}
		}
		remove_shortcode('embed', array($this, 'processEmbedCode'));
		foreach($replaceFrom as $i => $v) {
			$replaceFrom[ $i ] = '['. $v. ']';
		}
		return str_replace($replaceFrom, $replaceTo, $string);
	}
	public function processEmbedCode($attrs, $url = '') {
		if ( empty( $url ) && ! empty( $attrs['src'] ) ) {
			$url = trim($attrs['src']);
		}
		if(empty($url)) return false;
		return wp_oembed_get($url, $attrs);
	}
	protected function _initTwig() {
		if(!$this->_twig) {
			if(!class_exists('Twig_Autoloader')) {
				require_once(LCS_CLASSES_DIR. 'Twig'. DS. 'Autoloader.php');
			}
			Twig_Autoloader::register();
			$this->_twig = new Twig_Environment(new Twig_Loader_String(), array('debug' => 0));
			$this->_twig->addFunction(
				new Twig_SimpleFunction('adjBs', array(
						$this,
						'adjustBrightness'
					)
				)
			);
			$this->_twig->addFunction(
				new Twig_SimpleFunction('adjOp', array(
						$this,
						'adjustOpacity'
					)
				)
			);
		}
	}
	public function getChatPositions() {
		return array(
			'top_left' => __('Top Left', LCS_LANG_CODE),
			'top_center' => __('Top Center', LCS_LANG_CODE),
			'top_right' => __('Top Right', LCS_LANG_CODE),
			
			'bottom_left' => __('Bottom Left', LCS_LANG_CODE),
			'bottom_center' => __('Bottom Center', LCS_LANG_CODE),
			'bottom_right' => __('Bottom Right', LCS_LANG_CODE),
			
			/*'center_left' => __('Center Left', LCS_LANG_CODE),
			'center_right' => __('Center Right', LCS_LANG_CODE),*/
		);
	}
	public function getDashboardPage() {
		frameLcs::_()->getModule('templates')->loadJqGrid();
		$chatEngine = $this->getModel()->getCurrentEngine();
		
		frameLcs::_()->addScript('admin.chat.dashboard', $this->getModule()->getModPath(). 'js/admin.chat.dashboard.js');
		frameLcs::_()->addJSVar('admin.chat.dashboard', 'lcsChatListTblUrl', uriLcs::mod('chat', 'getListForTbl', array('reqType' => 'ajax', '_model' => 'chat_sessions')));
		frameLcs::_()->addJSVar('admin.chat.dashboard', 'lcsAgentChatSessionUrl', uriLcs::mod('chat', 'agentChat', array('baseUrl' => admin_url())));
		frameLcs::_()->addJSVar('admin.chat.dashboard', 'lcsChatEngine', $chatEngine);
		
		$allSessionStatuses = $this->getModel('chat_sessions')->getStatuses();
		$availableAgentStatusesSel = array('all' => __('All', LCS_LANG_CODE));
		foreach($allSessionStatuses as $sKey => $s) {
			$availableAgentStatusesSel[ $s['id'] ] = $s['label'];
		}
		$this->assign('availableAgentStatusesSel', $availableAgentStatusesSel);
		$this->assign('isAdmin', frameLcs::_()->getModule('user')->isAdmin());
		return parent::getContent('chatDashboard');
	}
	public function generateAgentChatPage( $sessionId ) {
		$session = $this->getModel('chat_sessions')->getById( $sessionId );
		if(empty($session)) {
			$this->pushError(__('Can not find Session', LCS_LANG_CODE));
			return $this->_getAgentsErrorsPage();
		}
		$engine = $this->getModel('chat_engines')->getById( $session['engine_id'] );
		if(empty($engine)) {
			$this->pushError(__('Can not find Engine', LCS_LANG_CODE));
			return $this->_getAgentsErrorsPage();
		}
		
		frameLcs::_()->getModule('templates')->init( true );
		frameLcs::_()->getModule('templates')->loadBootstrap();
		frameLcs::_()->getModule('templates')->loadJqueryUi();
		$this->loadCoreAssets();
		
		frameLcs::_()->addStyle('admin.chat.agent', $this->getModule()->getModPath(). 'css/admin.chat.agent.css');
		frameLcs::_()->addScript('admin.chat.agent', $this->getModule()->getModPath(). 'js/admin.chat.agent.js');
		frameLcs::_()->addJSVar('admin.chat.agent', 'lcsSession', $session);
		frameLcs::_()->addJSVar('admin.chat.agent', 'lcsEngine', $engine);
		
		frameLcs::_()->addStyles();
		frameLcs::_()->addScripts();
		
		$allSessionStatuses = $this->getModel('chat_sessions')->getStatuses();
		$availableAgentStatusesSel = array();
		foreach($allSessionStatuses as $sKey => $s) {
			$availableAgentStatusesSel[ $s['id'] ] = $s['label'];
		}
		$this->assign('availableAgentStatusesSel', $availableAgentStatusesSel);
		$this->assign('session', $session);
		$this->assign('engine', $engine);
		return parent::getContent('chatAgentPage');
	}
	public function loadCoreAssets() {
		frameLcs::_()->addScript('chat.core', $this->getModule()->getModPath(). 'js/chat.core.js');
		frameLcs::_()->addStyle('chat.core', $this->getModule()->getModPath(). 'css/chat.core.css');
	}
	private function _getAgentsErrorsPage() {
		$this->assign('errors', $this->getErrors());
		return parent::getContent('chatAgentsErrorsPage');
	}
	public function showExtraUserFields( $user = false ) {
		if(!is_object($user))
			$user = false;
		$chatUser = $user ? $this->getModel('chat_users')->getBy('wp_id', $user->ID, true) : false;
		$makeAgentCheck = $chatUser && !in_array($chatUser['position_code'], array('user')) && $chatUser['active'];
		if((int) reqLcs::getVar('lcs_agent')) {
			$makeAgentCheck = true;
		}
		$this->assign('user', $user);
		$this->assign('chatUser', $chatUser);
		$this->assign('makeAgentCheck', $makeAgentCheck);
		return parent::getContent('chatExtraUserFields');
	}
}
