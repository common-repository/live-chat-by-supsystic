<?php
class userLcs extends moduleLcs {
	protected $_data = array();
    protected $_curentID = 0;
	protected $_dataLoaded = false;
	
    public function loadUserData() {
        return $this->getCurrent();
    }
    public function isAdmin() {
		if(!function_exists('wp_get_current_user')) {
			frameLcs::_()->loadPlugins();
		}
        return current_user_can( frameLcs::_()->getModule('adminmenu')->getMainCap() );
    }
	public function getCurrentUserPosition() {
		if($this->isAdmin())
			return LCS_ADMIN;
		else if($this->isAgent())
			return LCS_AGENT;
		else if($this->getCurrentID())
			return LCS_LOGGED;
		else 
			return LCS_GUEST;
	}
	public function isAgent() {
		if($this->isAdmin())
			return true;
		$userId = $this->getCurrentID();
		if($userId) {
			$chatUser = frameLcs::_()->getModule('chat')->getModel('chat_users')->setWhere(array('active' => 1))->getBy('wp_id', $userId, true);
			if($chatUser && !in_array($chatUser['position_code'], array(LCS_USER))) {
				return true;
			}
		}
		return false;
	}
    public function getCurrent() {
		return wp_get_current_user();
    }
	
    public function getCurrentID() {
		$this->_loadUserData();
		return $this->_curentID;
    }
	protected function _loadUserData() {
		if(!$this->_dataLoaded) {
			if(!function_exists('wp_get_current_user')) frameLcs::_()->loadPlugins();
				$user = wp_get_current_user();
			$this->_data = $user->data;
			$this->_curentID = $user->ID;
			$this->_dataLoaded = true;
		}
	}
	public function getAdminsList() {
		global $wpdb;
		$admins = dbLcs::get('SELECT * FROM #__users 
			INNER JOIN #__usermeta ON #__users.ID = #__usermeta.user_id
			WHERE #__usermeta.meta_key = "#__capabilities" AND #__usermeta.meta_value LIKE "%administrator%"');
		return $admins;
	}
	public function isLoggedIn() {
		return is_user_logged_in();
	}
}

