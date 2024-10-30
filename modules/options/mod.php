<?php
class optionsLcs extends moduleLcs {
	private $_tabs = array();
	private $_options = array();
	private $_optionsToCategoires = array();	// For faster search
	
	public function init() {
		//dispatcherLcs::addAction('afterModulesInit', array($this, 'initAllOptValues'));
		add_action('init', array($this, 'initAllOptValues'), 99);	// It should be init after all languages was inited (frame::connectLang)
		//dispatcherLcs::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
	}
	public function initAllOptValues() {
		// Just to make sure - that we loaded all default options values
		$this->getAll();
	}
    /**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
    public function get($code) {
        return $this->getModel()->get($code);
    }
	/**
     * This method provides fast access to options model method get
     * @see optionsModel::get($d)
     */
	public function isEmpty($code) {
		return $this->getModel()->isEmpty($code);
	}
	public function getAllowedPublicOptions() {
		$allowKeys = array('add_love_link');
		$res = array();
		foreach($allowKeys as $k) {
			$res[ $k ] = $this->get($k);
		}
		return $res;
	}
	public function getAdminPage() {
		if(installerLcs::isUsed()) {
			return $this->getView()->getAdminPage();
		} else {
			installerLcs::setUsed();	// Show this welcome page - only one time
			return frameLcs::_()->getModule('supsystic_promo')->showWelcomePage();
		}
	}
	public function addAdminTab($tabs) {
		$tabs['settings'] = array(
			'label' => __('System', LCS_LANG_CODE), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'fa-gear', 'sort_order' => 30,
		);
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	public function getTabs() {
		if(empty($this->_tabs)) {
			$this->_tabs = dispatcherLcs::applyFilters('mainAdminTabs', array(
				//'main_page' => array('label' => __('Main Page', LCS_LANG_CODE), 'callback' => array($this, 'getTabContent'), 'wp_icon' => 'dashicons-admin-home', 'sort_order' => 0), 
			));
			foreach($this->_tabs as $tabKey => $tab) {
				if(!isset($this->_tabs[ $tabKey ]['url'])) {
					$this->_tabs[ $tabKey ]['url'] = $this->getTabUrl( $tabKey );
				}
			}
			uasort($this->_tabs, array($this, 'sortTabsClb'));
		}
		return $this->_tabs;
	}
	public function sortTabsClb($a, $b) {
		if(isset($a['sort_order']) && isset($b['sort_order'])) {
			if($a['sort_order'] > $b['sort_order'])
				return 1;
			if($a['sort_order'] < $b['sort_order'])
				return -1;
		}
		return 0;
	}
	public function getTab($tabKey) {
		$this->getTabs();
		return isset($this->_tabs[ $tabKey ]) ? $this->_tabs[ $tabKey ] : false;
	}
	public function getTabContent() {
		return $this->getView()->getTabContent();
	}
	public function getActiveTab() {
		$reqTab = reqLcs::getVar('tab');
		return empty($reqTab) ? LCS_DEFAULT_ADMIN_TAB : $reqTab;
	}
	public function getTabUrl($tab = '') {
		static $mainUrl;
		if(empty($mainUrl)) {
			$mainUrl = frameLcs::_()->getModule('adminmenu')->getMainLink();
		}
		return empty($tab) ? $mainUrl : $mainUrl. '&tab='. $tab;
	}
	public function getRolesList() {
		if(!function_exists('get_editable_roles')) {
			require_once( ABSPATH . '/wp-admin/includes/user.php' );
		}
		return get_editable_roles();
	}
	public function getAvailableUserRolesSelect() {
		$rolesList = $this->getRolesList();
		$rolesListForSelect = array();
		foreach($rolesList as $rKey => $rData) {
			$rolesListForSelect[ $rKey ] = $rData['name'];
		}
		return $rolesListForSelect;
	}
	public function getAll() {
		if(empty($this->_options)) {
			$this->_options = dispatcherLcs::applyFilters('optionsDefine', array(
				'general' => array(
					'label' => __('General', LCS_LANG_CODE),
					'opts' => array(
						'send_stats' => array('label' => __('Send usage statistics', LCS_LANG_CODE), 'desc' => __('Send information about what plugin options you prefer to use, this will help us make our solution better for You.', LCS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						//'disable_subscribe_ip_antispam' => array('label' => __('Disable blocking Subscription from same IP', LCS_LANG_CODE), 'desc' => __('By default our plugin have feature to block subscriptions from same IP more then one time per hour - to avoid spam subscribers. But you can disable this feature here.', LCS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						'add_love_link' => array('label' => __('Enable promo link', LCS_LANG_CODE), 'desc' => __('We are trying to make our plugin better for you, and you can help us with this. Just check this option - and small promotion link will be added in the bottom of your Chat. This is easy for you - but very helpful for us!', LCS_LANG_CODE), 'def' => '0', 'html' => 'checkboxHiddenVal'),
						//'access_roles' => array('label' => __('User role can use plugin', LCS_LANG_CODE), 'desc' => __('User with next roles will have access to whole plugin from admin area.', LCS_LANG_CODE), 'def' => 'administrator', 'html' => 'selectlist', 'options' => array($this, 'getAvailableUserRolesSelect'), 'pro' => ''),
					),
				),
			));
			$isPro = frameLcs::_()->getModule('supsystic_promo')->isPro();
			foreach($this->_options as $catKey => $cData) {
				foreach($cData['opts'] as $optKey => $opt) {
					$this->_optionsToCategoires[ $optKey ] = $catKey;
					if(isset($opt['pro']) && !$isPro) {
						$this->_options[ $catKey ]['opts'][ $optKey ]['pro'] = frameLcs::_()->getModule('supsystic_promo')->generateMainLink('utm_source=plugin&utm_medium='. $optKey. '&utm_campaign=chat');
					}
				}
			}
			$this->getModel()->fillInValues( $this->_options );
		}
		return $this->_options;
	}
	public function getFullCat($cat) {
		$this->getAll();
		return isset($this->_options[ $cat ]) ? $this->_options[ $cat ] : false;
	}
	public function getCatOpts($cat) {
		$opts = $this->getFullCat($cat);
		return $opts ? $opts['opts'] : false;
	}
	public function getOpt($optKey, $attrKey = '') {
		if(!isset($this->_optionsToCategoires[ $optKey ])) {
			if(LCS_DEBUG_MODE) {
				return '<h1 style="color: red;">CAN NOT FIND OPTION '. $optKey. '</h1>';
			}
			return false;
		}
		return empty($attrKey) 
			? $this->_options[ $this->_optionsToCategoires[ $optKey ] ]['opts'][ $optKey ]
			: $this->_options[ $this->_optionsToCategoires[ $optKey ] ]['opts'][ $optKey ][ $attrKey ];
	}
	public function renderOpt($optKey, $namePref = '') {
		if(!isset($this->_optionsToCategoires[ $optKey ])) {
			if(LCS_DEBUG_MODE) {
				return '<h1 style="color: red;">CAN NOT FIND OPTION '. $optKey. '</h1>';
			}
			return '';
		}
		$opt = $this->_options[ $this->_optionsToCategoires[ $optKey ] ]['opts'][ $optKey ];
		$htmlType = isset($opt['html']) ? $opt['html'] : false;
		if(empty($htmlType)) return '';
		$htmlOpts = array('value' => $opt['value'], 'attrs' => 'data-optkey="'. $optKey. '"');
		if(in_array($htmlType, array('selectbox', 'selectlist')) && isset($opt['options'])) {
			if(is_callable($opt['options'])) {
				$htmlOpts['options'] = call_user_func( $opt['options'] );
			} elseif(is_array($opt['options'])) {
				$htmlOpts['options'] = $opt['options'];
			}
		}
		if(isset($opt['pro']) && !empty($opt['pro'])) {
			$htmlOpts['attrs'] .= ' class="lcsProOpt"';
		}
		$inputName = $optKey;
		if(!empty($namePref)) {
			$inputName = $namePref. '['. $inputName. ']';
		}
		return htmlLcs::$htmlType($inputName, $htmlOpts);
	}
}

