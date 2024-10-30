<?php
class statisticsLcs extends moduleLcs {
	private $_types = array();
	public function init() {
		parent::init();
		dispatcherLcs::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
	}
	public function addAdminTab($tabs) {
		$tabTitle = __('Statistics', LCS_LANG_CODE);
		if(!frameLcs::_()->getModule('supsystic_promo')->isPro()) {
			$tabTitle .= '<span class="lcsMenuProLabel">Pro</span>';
		}
		$tabs[ $this->getCode() ] = array(
			'label' => $tabTitle, 'callback' => array($this, 'getTabContent'), 'fa_icon' => 'fa-line-chart', 'sort_order' => 25, //'is_main' => true,
		);
		return $tabs;
	}
	public function getTabContent() {
		if(frameLcs::_()->getModule('stat_graph')) {
			return frameLcs::_()->getModule('stat_graph')->getView()->displayAdminTabContent();
		} else {
			return $this->getView()->displayAdminPromoTabContent();
		}
	}
	public function getTypes() {
		if(empty($this->_types)) {
			$this->_types = array(
				'show' => array('id' => 1, 'label' => __('Chat Displayed', LCS_LANG_CODE)),
				'open' => array('id' => 2, 'label' => __('Chat Opened', LCS_LANG_CODE)),
				'registration' => array('id' => 3, 'label' => __('User Registered', LCS_LANG_CODE)),
				'session_start' => array('id' => 4, 'label' => __('Session Started', LCS_LANG_CODE)),
			);
		}
		return $this->_types;
	}
	public function getTypeIdByCode($code) {
		$this->getTypes();
		return isset($this->_types[ $code ]) ? $this->_types[ $code ]['id'] : false;
	}
}