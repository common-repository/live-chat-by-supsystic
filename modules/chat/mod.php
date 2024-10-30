<?php
class chatLcs extends moduleLcs {
	private $_renderedIds = array();

	private $_assetsUrl = '';
	private $_oldAssetsUrl = 'https://supsystic.com/_assets/live-chat/';
	
	private $_dashboardSlug = 'chat-wp-supsystic-dashboard';

	public function init() {
		//$this->_checkDefaultEngine();
		dispatcherLcs::addFilter('mainAdminTabs', array($this, 'addAdminTab'));
		add_action('template_redirect', array($this, 'checkChatShow'));
		// Add Dashboard menu
		add_action('admin_menu', array($this, 'addDashboard'), 9);
		// Modify user profile
		add_action('show_user_profile', array($this, 'showExtraUserFields'));
		add_action('edit_user_profile', array($this, 'showExtraUserFields'));
		add_action('user_new_form', array($this, 'showExtraUserFields'));
		
		add_action('personal_options_update', array($this, 'saveExtraUserFields'));
		add_action('edit_user_profile_update', array($this, 'saveExtraUserFields'));
		add_action('edit_user_created_user', array($this, 'saveExtraUserFields'));
		// Add shortcodes
		add_shortcode(LCS_SHORTCODE_CLICK, array($this, 'showChatOnClick'));
		add_action('wp_footer', array($this, 'collectFooterRender'), 0);
		add_filter('wp_nav_menu_objects', array($this, 'checkMenuItemsForChats'));
		// Check login/logout process
		add_filter('login_redirect', array($this, 'checkUserLogin'), 10, 3);
		add_filter('wp_logout', array($this, 'checkUserLogout'));
	}
	public function addAdminTab($tabs) {
		$tabs[ $this->getCode(). '-settings' ] = array(
			'label' => __('Settings', LCS_LANG_CODE), 'callback' => array($this, 'getSettingsTabContent'), 'fa_icon' => 'fa-gear', 'sort_order' => 20, //'is_main' => true,
		);
		return $tabs;
	}
	public function getSettingsTabContent() {
		return $this->getView()->getSettingsTabContent();
	}
	public function checkChatShow() {
		global $wp_query;
		$currentPageId = (int) get_the_ID();
		$isHome = is_home();
		/*show_pages = 1 -> All, 2 -> show on selected, 3 -> do not show on selected*/
		/*show_on = 1 -> Page load, 2 -> click on page, 3 -> click on certain element (shortcode)*/
		$condition = "active = 1 AND (show_pages = 1";
		$havePostsListing = $wp_query && is_object($wp_query) && isset($wp_query->posts) && is_array($wp_query->posts) && !empty($wp_query->posts);
		// Check if we can show chat on this page
		if(($currentPageId && $havePostsListing && count($wp_query->posts) == 1) || $isHome) {
			if($isHome)
				$currentPageId = LCS_HOME_PAGE_ID;
			$condition .= " OR (show_pages = 2 AND id IN (SELECT engine_id FROM @__chat_engines_show_pages WHERE post_id = $currentPageId AND not_show = 0))
				OR (show_pages = 3 AND id NOT IN (SELECT engine_id FROM @__chat_engines_show_pages WHERE post_id = $currentPageId AND not_show = 1))";
		}
		$condition .= ")";
		// Check if there are chats that need to be rendered by click on some element
		$condition .= " AND (show_on != 3";
		if($havePostsListing) {
			$allowForPosts = array();
			// Check if show chat shortcode or at least it's show js function lcsShowChat() - exists on any post content
			foreach($wp_query->posts as $post) {
				if(is_object($post) && isset($post->post_content)) {
					if((preg_match_all('/\[\s*'. LCS_SHORTCODE_CLICK. '.+id\s*\=.*(?P<CHAT_ID>\d+)\]/iUs', $post->post_content, $matches) 
						|| preg_match_all('/'. LCS_JS_FUNC_CLICK. '\s*\(\s*(?P<CHAT_ID>\d+)\s*\)\s*;*/iUs', $post->post_content, $matches)
						|| preg_match_all('/\"\#'. LCS_JS_FUNC_CLICK. '_(?P<CHAT_ID>\d+)\"/iUs', $post->post_content, $matches)
						) && isset($matches['CHAT_ID'])
					) {
						if(!is_array($matches['CHAT_ID']))
							$matches['CHAT_ID'] = array( $matches['CHAT_ID'] );
						$matches['CHAT_ID'] = array_map('intval', $matches['CHAT_ID']);
						$allowForPosts = array_merge($allowForPosts, $matches['CHAT_ID']);
					}
				}
			}
			if(!empty($allowForPosts)) {
				$condition .= " OR id IN (". implode(',', $allowForPosts). ")";
			}
		}
		$condition .= ")";
		$condition = dispatcherLcs::applyFilters('chatCheckCondition', $condition);
		
		$engines = $this->_beforeRender( $this->getModel('chat_engines')->addWhere( $condition )->getFromTbl() );
 		if(!empty($engines)) {
			$this->renderList( $engines );
		}
	}
	private function _beforeRender($engines) {
		global $wp_query;
		$dataRemoved = false;
		if(!empty($engines)) {
			$mobileDetect = NULL;
			$isMobile = false;
			$isTablet = false;
			$isDesktop = false;
			$isUserLoggedIn = frameLcs::_()->getModule('user')->isLoggedIn();
			$postType = false;
			
			$userIp = false;
			$countryCode = false;
			$langCode = false;
			$ts = false;
			
			foreach($engines as $i => $e) {
				if(isset($e['params']['main']['hide_for_devices']) 
					&& !empty($e['params']['main']['hide_for_devices'])
				) {	// Check if engine need to be hidden for some devices
					if(!$mobileDetect) {
						importClassLcs('Mobile_Detect', LCS_HELPERS_DIR. 'mobileDetect.php');
						$mobileDetect = new Mobile_Detect();
						$isTablet = $mobileDetect->isTablet();
						$isMobile = !$isTablet && $mobileDetect->isMobile();
						$isDesktop = !$isMobile && !$isTablet;
					}
					$hideShowRevert = isset($e['params']['main']['hide_for_devices_show']) && (int) $e['params']['main']['hide_for_devices_show'];
					if((!$hideShowRevert && in_array('mobile', $e['params']['main']['hide_for_devices']) && $isMobile)
						|| ($hideShowRevert && !in_array('mobile', $e['params']['main']['hide_for_devices']) && $isMobile)
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					} elseif((!$hideShowRevert && in_array('tablet', $e['params']['main']['hide_for_devices']) && $isTablet)
						|| ($hideShowRevert && !in_array('tablet', $e['params']['main']['hide_for_devices']) && $isTablet)
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					} elseif((!$hideShowRevert && in_array('desktop', $e['params']['main']['hide_for_devices']) && $isDesktop)
						|| ($hideShowRevert && !in_array('desktop', $e['params']['main']['hide_for_devices']) && $isDesktop)
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
				if(isset($e['params']['main']['hide_for_post_types'])
					&& !empty($e['params']['main']['hide_for_post_types'])
				) { // Check if chat need to be hidden for some post types
					if(!$postType) {
						$postType = get_post_type();
					}
					$hideShowRevert = isset($e['params']['main']['hide_for_post_types_show']) && (int) $e['params']['main']['hide_for_post_types_show'];
					if(((!$hideShowRevert && count($wp_query->posts) === 1 && in_array($postType, $e['params']['main']['hide_for_post_types'])) 
						|| ($hideShowRevert && (!in_array($postType, $e['params']['main']['hide_for_post_types']) || count($wp_query->posts) !== 1))
					)) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
				if(isset($e['params']['main']['hide_for_logged_in']) 
					&& !empty($e['params']['main']['hide_for_logged_in'])
					&& $isUserLoggedIn
				) {	// Check if we need to hide it from logged-in users
					unset($engines[ $i ]);
					$dataRemoved = true;
				}
				if(isset($e['params']['main']['hide_for_ips']) 
					&& !empty($e['params']['main']['hide_for_ips'])
				) {	// Check if we need to hide it for IPs
					$hideForIpsArr = array_map('trim', explode(',', $e['params']['main']['hide_for_ips']));
					if(!empty($hideForIpsArr)) {
						if(!$userIp) {
							$userIp = utilsLcs::getIP();
						}
						$hideShowRevert = isset($e['params']['main']['hide_for_ips_show']) && (int) $e['params']['main']['hide_for_ips_show'];
						if((!$hideShowRevert && in_array($userIp, $hideForIpsArr)) 
							|| ($hideShowRevert && !in_array($userIp, $hideForIpsArr))
						) {
							unset($engines[ $i ]);
							$dataRemoved = true;
						}
					}
				}
				if(isset($e['params']['main']['hide_for_countries']) 
					&& !empty($e['params']['main']['hide_for_countries'])
				) {	// Check if we need to hide it for Counties
					if(!$countryCode) {
						$countryCode = $this->getCountryCode();
					}
					$hideShowRevert = isset($e['params']['main']['hide_for_countries_show']) && (int) $e['params']['main']['hide_for_countries_show'];
					if((!$hideShowRevert && in_array($countryCode, $e['params']['main']['hide_for_countries']))
						|| ($hideShowRevert && !in_array($countryCode, $e['params']['main']['hide_for_countries']))
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
				if(isset($e['params']['main']['hide_for_languages']) 
					&& !empty($e['params']['main']['hide_for_languages'])
				) {	// Check if we need to hide it for Languages
					if(!$langCode) {
						$langCode = utilsLcs::getBrowserLangCode();
					}
					$hideShowRevert = isset($e['params']['main']['hide_for_languages_show']) && (int) $e['params']['main']['hide_for_languages_show'];
					if((!$hideShowRevert && in_array($langCode, $e['params']['main']['hide_for_languages']))
						|| ($hideShowRevert && !in_array($langCode, $e['params']['main']['hide_for_languages']))
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
				if(isset($e['params']['main']['enb_show_date']) 
					&& !empty($e['params']['main']['enb_show_date'])
				) {	// Check if we need to show it in Date range
					$tsFrom = isset($e['params']['main']['show_date_from']) && !empty($e['params']['main']['show_date_from'])
						? strtotime($e['params']['main']['show_date_from'])
						: false;
					$tsTo = isset($e['params']['main']['show_date_to']) && !empty($e['params']['main']['show_date_to'])
						? strtotime($e['params']['main']['show_date_to'])
						: false;
					if(!$ts) {
						$ts = time();
					}
					if(($tsFrom && $ts < $tsFrom) 
						|| ($tsTo && $ts > $tsTo)
					) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
				if(isset($e['params']['main']['enb_show_days']) 
					&& !empty($e['params']['main']['enb_show_days'])
					&& isset($e['params']['main']['show_days']) 
					&& !empty($e['params']['main']['show_days'])
				) {	// Show only for selected days
					$currDayId = utilsLcs::getWeekDaysArray( true );
					if(!in_array($currDayId, $e['params']['main']['show_days'])) {
						unset($engines[ $i ]);
						$dataRemoved = true;
					}
				}
			}
		}
		if($dataRemoved) {
			$engines = array_values( $engines );
		}
		if(!empty($engines)) {
			reqLcs::startSession();
			
			global $wp_query;
			$currentPageId = (int) get_the_ID();
			$isHome = is_home();
			$havePostsListing = $wp_query && is_object($wp_query) && isset($wp_query->posts) && is_array($wp_query->posts) && !empty($wp_query->posts);
			$aggentsLogged = null;
			
			foreach($engines as $i => $e) {
				$engines[ $i ]['tpl'] = $this->getModel('chat_templates')->getForEngine( $engines[ $i ]['id'] );
				$engines[ $i ]['triggers'] = $this->getModel('chat_triggers')->getBy('engine_id', $engines[ $i ]['id']);
				if(!empty($engines[ $i ]['triggers'])) {
					$engines[ $i ]['current_state'] = array();
					if($aggentsLogged === null) {
						$aggentsLogged = frameLcs::_()->getModule('options')->get('agents_logged');
					}
					$engines[ $i ]['current_state']['agent_online'] = !empty($aggentsLogged);
					if(($currentPageId && $havePostsListing && count($wp_query->posts) == 1) || $isHome) {
						if($isHome)
							$currentPageId = LCS_HOME_PAGE_ID;
						$engines[ $i ]['current_state']['page_id'] = $currentPageId;
					}
					if(!$countryCode) {
						$countryCode = $this->getCountryCode();
					}
					$engines[ $i ]['current_state']['country_code'] = $countryCode;
					$engines[ $i ]['current_state']['date'] = date( LCS_DATE_FORMAT. ' '. LCS_TIME_FORMAT );
					foreach($engines[ $i ]['triggers'] as $k => $t) {
						if(!empty($engines[ $i ]['triggers'][ $k ]['actions'])) {
							foreach($engines[ $i ]['triggers'][ $k ]['actions'] as $j => $a) {
								if(isset($a['enb']) 
									&& $a['enb'] 
									&& isset($a['anim_key']) 
									&& $a['anim_key'] != 'none'
								) {
									$engines[ $i ]['triggers'][ $k ]['actions'][ $j ]['anim'] = $this->getView()->getAnimationByKey( $a['anim_key'] );
								}
								/*if($engines[ $i ]['triggers'][ $k ]['actions'][ $j ]['code'] == LCS_AUTO_START) {
									$engines[ $i ]['triggers'][ $k ]['actions'][ $j ]['connect_hash'] = md5(date('m-d-Y'). $engines[ $i ]['id']. NONCE_KEY. LCS_AUTO_START);
								}*/
							}
						}
					}
					
				}
			}
			$engines = dispatcherLcs::applyFilters('enginesListBeforeRender', $engines);
		}
		return $engines;
	}
	public function checkConnectHash($hash, $id) {
		return md5(date('m-d-Y'). $id. NONCE_KEY) == $hash;
	}
	public function generateConnectHash($id) {
		return md5(date('m-d-Y'). $id. NONCE_KEY);
	}
	public function renderList($engines, $jsListVarName = 'lcsEngines') {
		static $renderedBefore = false;
		$engines = dispatcherLcs::applyFilters('chatListBeforeRender', $engines);
		foreach($engines as $i => $e) {
			if(isset($e['params']['anim_key']) && !empty($e['params']['anim_key']) && $e['params']['anim_key'] != 'none') {
				$engines[ $i ]['params']['anim'] = $this->getView()->getAnimationByKey( $e['params']['anim_key'] );
			}
			if(isset($e['params']['anim_duration']) && !empty($e['params']['anim_duration'])) {
				$engines[ $i ]['params']['anim_duration'] = (float) $e['params']['anim_duration'];
			}
			if(!isset($e['params']['anim_duration']) || $e['params']['anim_duration'] <= 0) {
				$engines[ $i ]['params']['anim_duration'] = 1000;	// 1 second by default
			}
			$engines[ $i ]['tpl']['rendered_html'] = $this->getView()->generateHtml( $engines[ $i ]['tpl'], array('replace_style_tag' => true) );
			// Unset those parameters - make data lighter
			unset($engines[ $i ]['tpl']['css']);
			unset($engines[ $i ]['tpl']['html']);
			$engines[ $i ]['connect_hash'] = $this->generateConnectHash( $engines[ $i ]['id'] );
			$this->_renderedIds[] = $e['id'];
		}
		if(!$renderedBefore) {
			frameLcs::_()->getModule('templates')->loadCoreJs();
			frameLcs::_()->getModule('templates')->loadFontAwesome();
			
			$this->getView()->loadCoreAssets();
			frameLcs::_()->addScript('frontend.chat', $this->getModPath(). 'js/frontend.chat.js');
			frameLcs::_()->addJSVar('frontend.chat', $jsListVarName, $engines);
			frameLcs::_()->addStyle('frontend.chat', $this->getModPath(). 'css/frontend.chat.css');
			// Detect what animation library should be loaded. Be advised that they can be used both in same time.
			$loadAnims = true;
			// Always load it for now
			/*foreach($engines as $e) {
				if($loadAnims) break;
				if(isset($e['params'], $e['params'], $e['params']['anim']) && !empty($e['params']['anim'])) {
					$loadAnims = true;
				}
			}*/
			if($loadAnims) {
				frameLcs::_()->getModule('templates')->loadCssAnims();
			}
			// Check draggable load JS here
			$loadDraggable = false;
			foreach($engines as $i => $e) {
				if(isset($e['params']['enb_draggable']) && $e['params']['enb_draggable']) {
					$loadDraggable = true;
					break;
				}
			}
			if($loadDraggable) {
				frameLcs::_()->getModule('templates')->loadDraggable();
			}
			$renderedBefore = true;
		} else {
			// We use such "un-professional" method - because in comon - we don't want to collect data for wp_footer output - because unfortunatelly not all themes has it, 
			// so, to make it work for most part of users - we try to out all scripts before footer
			// but some chats wil still need this - wp_footer for example - additional output - so that's why it is here
			frameLcs::_()->addScript('frontend.dummy.chat', $this->getModPath(). 'js/frontend.dummy.chat.js');
			frameLcs::_()->addJSVar('frontend.dummy.chat', $jsListVarName, $engines);
		}
	}
	public function collectFooterRender() {
		if(!empty($this->_addToFooterIds)) {
			$idsToRender = array();
			foreach($this->_addToFooterIds as $id) {
				if((!empty($this->_renderedIds) && in_array($id, $this->_renderedIds)) || in_array($id, $idsToRender)) continue;
				$idsToRender[] = $id;
			}
			if(!empty($idsToRender)) {
				$engines = $this->_beforeRender( $this->getModel('chat_engines')->addWhere('id IN ('. implode(',', $idsToRender). ')')->getFromTbl() );
				if(!empty($engines)) {
					$this->renderList( $engines, 'lcsChatsFromFooter' );
				}
			}
		}
	}
	public function showChatOnClick($params) {
		$id = isset($params['id']) ? (int) $params['id'] : 0;
		if(!$id && isset($params[0]) && !empty($params[0])) {	// For some reason - for some cases it convert space in shortcode - to %20 im this place
			$id = explode('=', $params[0]);
			$id = isset($id[1]) ? (int) $id[1] : 0;
		}
		$this->_addToFooterIds[] = $id;
		return '#'. LCS_JS_FUNC_CLICK. '_'. $id;
	}
	public function checkMenuItemsForChats($menuItems) {
		if(!empty($menuItems)) {
			foreach($menuItems as $item) {
				if(is_object($item) && isset($item->attr_title) && !empty($item->attr_title) && strpos($item->attr_title, '#'. LCS_JS_FUNC_CLICK. '_') !== false) {
					preg_match('/\#'. LCS_JS_FUNC_CLICK. '_(\d+)/', $item->attr_title, $matched);
					$chatId = isset($matched[1]) ? (int) $matched[1] : 0;
					if($chatId) {
						$this->_addToFooterIds[] = $chatId;
					}
				}
			}
		}
		return $menuItems;
	}
	public function getCountryCode( $ip = false ) {
		importClassLcs('SxGeo', LCS_HELPERS_DIR. 'SxGeo.php');
		$sxGeo = new SxGeo(LCS_FILES_DIR. 'SxGeo.dat');
		if(!$ip)
			$ip = utilsLcs::getIP ();
		return $sxGeo->getCountry($ip);
	}
	public function getAssetsUrl() {
		if(empty($this->_assetsUrl)) {
			$this->_assetsUrl = frameLcs::_()->getModule('templates')->getCdnUrl(). '_assets/live-chat/';
		}
		return $this->_assetsUrl;
	}
	public function getOldAssetsUrl() {
		return $this->_oldAssetsUrl;
	}
	private function _checkDefaultEngine() {
		/*if(is_admin()) {
			$totalEngines = (int)$this->getModel('chat_engines')->getCount();
			if(empty($totalEngines)) {
				$this->getModel('chat_engines')->createDefaultEngine();
			}
		}*/
	}
	public function setCurrentUserId( $uid ) {
		reqLcs::setVar('lcs_curr_uid', $uid, 'session');
	}
	public function getCurrentUserId() {
		return (int) reqLcs::getVar('lcs_curr_uid', 'session');
	}
	public function setSessionId( $sid ) {
		reqLcs::setVar('lcs_curr_sid', $sid, 'session');
	}
	public function getSessionId( $checkInDb = false ) {
		$sid = (int) reqLcs::getVar('lcs_curr_sid', 'session');
		if($sid && $checkInDb) {
			$session = $this->getModel('chat_sessions')->getById( $sid );
			if(!$session) {	// Was removed from DB - clear it from session storage too
				$this->clearSessionId();
				$sid = 0;
			}
		}
		return $sid;
	}
	public function getSession() {
		$sid = $this->getSessionId( false );
		if($sid) {
			return $this->getModel('chat_sessions')->getById( $sid );
		}
		return false;
	}
	public function setAutoMsgSent() {
		reqLcs::setVar('lcs_auto_msg_sent', 1, 'session');
	}
	public function getAutoMsgSent() {
		return (int) reqLcs::getVar('lcs_auto_msg_sent', 'session');
	}
	public function clearSessionId() {
		reqLcs::clearVar('lcs_curr_sid', 'session');
	}
	public function addDashboard() {
		if(frameLcs::_()->getModule('user')->isAgent()) {
			$mainCap = 'read';	// Will be visible for all, but then can be removed
			$mainSlug = $this->_dashboardSlug;
			$mainMenuPageOptions = array(
				'page_title' => __('Live Chat Dashboard', LCS_LANG_CODE), 
				'menu_title' => __('Live Chat Dashboard', LCS_LANG_CODE), 
				'capability' => $mainCap,
				'menu_slug' => $mainSlug,
				'function' => array($this, 'getDashboardPage'));
			add_menu_page($mainMenuPageOptions['page_title'], $mainMenuPageOptions['menu_title'], $mainMenuPageOptions['capability'], $mainMenuPageOptions['menu_slug'], $mainMenuPageOptions['function'], 'dashicons-tickets');
		}
	}
	/*public function currUserCanSeeDashboard() {
		$userId = frameLcs::_()->getModule('users')->getCurrentID();
		if($userId) {
			$chatUser = frameLcs::_()->getModule('chat')->getModel()->getById( $userId );
		}
		return false;
	}*/
	public function getDashboardPage() {
		echo $this->getView()->getDashboardPage();
	}
	public function showExtraUserFields( $user = false ) {
		if(frameLcs::_()->getModule('user')->isAdmin()){
			echo $this->getView()->showExtraUserFields( $user );
		}
	}
	/*public function saveExtraUserFieldsFromNew($errors, $update, $user) {
		if ( is_wp_error( $errors->errors ) && ! empty( $errors->errors ) && $user && is_object($user) && $user->user_email ) 
			return;
		$wpUser = get_user_by('email', $user->user_email);
		var_dump($user->user_email, $wpUser); exit();
		if($wpUser && !is_wp_error($wpUser)) {
			$this->saveExtraUserFields( $wpUser->ID );
		}
	}*/
	public function saveExtraUserFields( $userId ) {
		if ( !current_user_can( 'edit_user', $userId ) )
			return false;
		if(frameLcs::_()->getModule('user')->isAdmin()) {
			$chatUser = $this->getModel('chat_users')->getBy('wp_id', $userId, true);
			$post = reqLcs::get('post');
			$lcsData = isset($post['lcs']) ? $post['lcs'] : array();
			$makeAgent = (int) (isset($lcsData['make_agent']) && $lcsData['make_agent']);
			
			if($chatUser || $makeAgent) {
				$userModel = $this->getModel('chat_users');
				$newPositionId = 0;
				if($chatUser) {
					$newPositionId = $chatUser['position'];
				} elseif($makeAgent) {
					$newPositionId = $userModel->getPositionId('agent');
				}
				$updateUserData = array(
					'name' => isset($post['display_name']) && !empty($post['display_name']) ? $post['display_name'] : $post['user_login'],
					'email' => $post['email'],
					'active' => $makeAgent ? 1 : 0,
					'position' => $newPositionId,
					'wp_id' => $userId,
				);
				if(!$chatUser) {
					$updateUserData['ip'] = utilsLcs::getIP();
				}
				$chatUser
					? $userModel->updateById($updateUserData, $chatUser['id'])
					: $userModel->insert($updateUserData);
			}
		}
	}
	public function checkUserLogin($redirectTo, $request, $user) {
		if($user && !is_wp_error($user)) {
			$chatUser = $this->getModel('chat_users')->getBy('wp_id', $user->ID, true);
			if($chatUser && !in_array($chatUser['position_code'], array(LCS_USER))) {
				$currLoggedIn = frameLcs::_()->getModule('options')->get('agents_logged');
				if(!$currLoggedIn) {
					$currLoggedIn = array();
				}
				$currLoggedIn[ $chatUser['id'] ] = time();
				frameLcs::_()->getModule('options')->getModel()->save('agents_logged', $currLoggedIn);
			}
		}
		return $redirectTo;
	}
	public function checkUserLogout() {
		$chatUser = $this->getModel('chat_users')->getCurrentAgent();
		$currLoggedIn = frameLcs::_()->getModule('options')->get('agents_logged');
		if($currLoggedIn && isset($currLoggedIn[ $chatUser['id'] ])) {
			unset($currLoggedIn[ $chatUser['id'] ]);
			frameLcs::_()->getModule('options')->getModel()->save('agents_logged', $currLoggedIn);
		}
	}
	public function isChatUserAgent( $chatUser ) {
		return $chatUser && !in_array($chatUser['position_code'], array(LCS_USER));
	}
}

