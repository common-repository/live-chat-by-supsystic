<?php
class supsystic_promoControllerLcs extends controllerLcs {
    public function welcomePageSaveInfo() {
		$res = new responseLcs();
		installerLcs::setUsed();
		if($this->getModel()->welcomePageSaveInfo(reqLcs::get('get'))) {
			$res->addMessage(__('Information was saved. Thank you!', LCS_LANG_CODE));
		} else {
			$res->pushError($this->getModel()->getErrors());
		}
		$originalPage = reqLcs::getVar('original_page');
		$http = isset($_SERVER['HTTPS']) && !empty($_SERVER['HTTPS']) ? 'https://' : 'http://';
		if(strpos($originalPage, $http. $_SERVER['HTTP_HOST']) !== 0) {
			$originalPage = '';
		}
		redirectLcs($originalPage);
	}
	public function sendContact() {
		$res = new responseLcs();
		$time = time();
		$prevSendTime = (int) get_option(LCS_CODE. '_last__time_contact_send');
		if($prevSendTime && ($time - $prevSendTime) < 5 * 60) {	// Only one message per five minutes
			$res->pushError(__('Please don\'t send contact requests so often - wait for response for your previous requests.'));
			$res->ajaxExec();
		}
        $data = reqLcs::get('post');
        $fields = $this->getModule()->getContactFormFields();
		foreach($fields as $fName => $fData) {
			$validate = isset($fData['validate']) ? $fData['validate'] : false;
			$data[ $fName ] = isset($data[ $fName ]) ? trim($data[ $fName ]) : '';
			if($validate) {
				$error = '';
				foreach($validate as $v) {
					if(!empty($error))
						break;
					switch($v) {
						case 'notEmpty':
							if(empty($data[ $fName ])) {
								$error = $fData['html'] == 'selectbox' ? __('Please select %s', LCS_LANG_CODE) : __('Please enter %s', LCS_LANG_CODE);
								$error = sprintf($error, $fData['label']);
							}
							break;
						case 'email':
							if(!is_email($data[ $fName ])) 
								$error = __('Please enter valid email address', LCS_LANG_CODE);
							break;
					}
					if(!empty($error)) {
						$res->pushError($error, $fName);
					}
				}
			}
		}
		if(!$res->error()) {
			$msg = 'Message from: '. get_bloginfo('name').', Host: '. $_SERVER['HTTP_HOST']. '<br />';
			$msg .= 'Plugin: '. LCS_WP_PLUGIN_NAME. '<br />';
			foreach($fields as $fName => $fData) {
				if(in_array($fName, array('name', 'email', 'subject'))) continue;
				if($fName == 'category')
					$data[ $fName ] = $fData['options'][ $data[ $fName ] ];
                $msg .= '<b>'. $fData['label']. '</b>: '. nl2br($data[ $fName ]). '<br />';
            }
			if(frameLcs::_()->getModule('mail')->send('support@supsystic.team.zendesk.com', $data['subject'], $msg, $data['name'], $data['email'])) {
				update_option(LCS_CODE. '_last__time_contact_send', $time);
			} else {
				$res->pushError( frameLcs::_()->getModule('mail')->getMailErrors() );
			}
			
		}
        $res->ajaxExec();
	}
	public function addNoticeAction() {
		$res = new responseLcs();
		$code = reqLcs::getVar('code', 'post');
		$choice = reqLcs::getVar('choice', 'post');
		if(!empty($code) && !empty($choice)) {
			$optModel = frameLcs::_()->getModule('options')->getModel();
			switch($choice) {
				case 'hide':
					$optModel->save('hide_'. $code, 1);
					break;
				case 'later':
					$optModel->save('later_'. $code, time());
					break;
				case 'done':
					$optModel->save('done_'. $code, 1);
					if($code == 'enb_promo_link_msg') {
						$optModel->save('add_love_link', 1);
					}
					break;
			}
			$this->getModel()->saveUsageStat($code. '.'. $choice, true);
			$this->getModel()->checkAndSend( true );
		}
		$res->ajaxExec();
	}
	public function exportForDb() {
		$forPro = (int) reqLcs::getVar('for_pro', 'get');
		$tblsCols = array(
			'@__chat_templates' => array('unique_id','label','original_id','engine_id','is_pro','params','html','css','img_preview','sort_order','date_created'),
			'@__chat_engines' => array('id','label','params','show_on','show_to','show_pages'),
		);
		$where = array(
			'@__chat_templates' => 'original_id = 0 AND is_pro = '. ($forPro ? '1' : '0'),
			'@__chat_engines' => 'id = '. LCS_DEF_ENGINE_ID
		);
		if($forPro) {
			echo 'db_install=>';
			foreach($tblsCols as $tbl => $cols) {
				if(in_array($tblsCols, array('@__chat_engines'))) continue;	// We don't need engines in PRO export
				echo $this->_makeExportQueriesLogicForPro($tbl, $cols, $where[ $tbl ]);
			}
		} else {
			foreach($tblsCols as $tbl => $cols) {
				echo $this->_makeExportQueriesLogic($tbl, $cols, $where[ $tbl ]);
			}
		}
		echo $this->_exportBaseTriggers();
		exit();
	}
	private function _exportBaseTriggers() {
		$exIds = array(6);
		$eol = "\r\n";
		$selectFields = array(
			'@__chat_triggers' => array('id', 'label', 'active', 'engine_id', 'actions'),
			'@__chat_triggers_conditions' => array('id', 'trigger_id', 'type', 'equal', 'value', 'sort_order'),
		);
		$triggers = dbLcs::get("SELECT ". implode(',', $selectFields['@__chat_triggers']). " FROM @__chat_triggers WHERE id IN (". implode(',', $exIds). ")");
		$triggersConditions = dbLcs::get("SELECT ". implode(',', $selectFields['@__chat_triggers_conditions']). " FROM @__chat_triggers_conditions WHERE trigger_id IN (". implode(',', $exIds). ")");
		$sqlTrVals = $sqlTrCondVals = array();
		
		foreach($triggers as $i => $t) {
			$tId = $t['id'];
			$t['id'] = $i + 1;
			$sqlTrVals[] = '("'. implode('","', $t). '")';
			foreach($triggersConditions as $j => $c) {
				if($c['trigger_id'] == $tId) {
					$c['trigger_id'] = $t['id'];
					$c['id'] = $j + 1;
					$sqlTrCondVals[] = '("'. implode('","', $c). '")';
				}
			}
		}
		$sqlTr = "INSERT INTO @__chat_triggers (". implode(',', $selectFields['@__chat_triggers']). ") VALUES ". implode(',', $sqlTrVals);
		$sqlTrCond = "INSERT INTO @__chat_triggers_conditions (". implode(',', $selectFields['@__chat_triggers_conditions']). ") VALUES ". implode(',', $sqlTrCondVals);
		return "dbLcs::query('". $sqlTr. "')"
			. $eol
			. "dbLcs::query('". $sqlTrCond. "')";
	}
	private function _makeExportQueriesLogicForPro($table, $cols, $where = '') {
		global $wpdb;
		$octoList = $this->_getExportData($table, $cols, true);
		$res = array();

		foreach($octoList as $octo) {
			$uId = '';
			$rowData = array();
			foreach($octo as $k => $v) {
				if(!in_array($k, $cols)) continue;
				$val = $wpdb->_real_escape($v);
				if($k == 'unique_id') $uId = $val;
				$rowData[ $k ] = $val;
			}
			$res[ $uId ] = $rowData;
		}
		echo str_replace(array('@__'), '', $table). '|'. base64_encode( utilsLcs::serialize($res) );
	}
	/*
	 * Set default engine properties here
	 */
	private function _prepareExportEngines($res) {
		foreach($res as $i => $r) {
			$res[ $i ]['show_on'] = 1;
			$res[ $i ]['show_to'] = 1;
			$res[ $i ]['show_pages'] = 1;
			$res[ $i ]['params'] = utilsLcs::unserialize( base64_decode($res[ $i ]['params']) );
			
			$res[ $i ]['params']['main'] = array(
				'show_on' => 'page_load',
				'show_pages' => 'all',
				'show_to' => 'everyone',
				
			);
			$res[ $i ]['params']['reg_fields'] = array(
				array('label' => __('E-Mail', LCS_LANG_CODE), 'html' => 'text', 'enb' => 1, 'mandatory' => 1, 'name' => 'email', 'html' => 'text'),
				array('label' => __('Name', LCS_LANG_CODE), 'html' => 'text', 'enb' => 1, 'name' => 'name', 'html' => 'text'),
			);
			$res[ $i ]['params']['reg_type'] = 'ask';
			$res[ $i ]['params']['reg_event'] = 'before_chat';
			
			$res[ $i ]['params']['chat_position'] = 'bottom_right';
			$res[ $i ]['params']['send_btn_txt'] = __('Send', LCS_LANG_CODE);
			$res[ $i ]['params']['wait_txt'] = __('Please wait while our operator will start conversation', LCS_LANG_CODE);
			$res[ $i ]['params']['chat_header_txt'] = __('<a href="https://supsystic.com/" target="_blank">supsystic.com</a> chat engine', LCS_LANG_CODE);
			$res[ $i ]['params']['complete_txt'] = __('Thank you for using our support! Driven by <a href="https://supsystic.com/" target="_blank">supsystic.com chat engine</a>', LCS_LANG_CODE);
			$res[ $i ]['params']['chat_agent_joined_txt'] = __('Operator [name] joined chat with you', LCS_LANG_CODE);
			$res[ $i ]['params']['register_btn_txt'] = __('Start Chat!', LCS_LANG_CODE);
			$res[ $i ]['params']['before_reg_txt'] = __('Please register before chat', LCS_LANG_CODE);
			$res[ $i ]['params']['chat_padding'] = 40;
			$res[ $i ]['params']['enb_sound'] = 1;
			unset($res[ $i ]['params']['sound']);
			$res[ $i ]['params']['msg_placeholder'] = __('Ask me here...', LCS_LANG_CODE);
			$res[ $i ]['params']['enb_agent_avatar'] = 1;
			$res[ $i ]['params']['enb_rating'] = 0;
			$res[ $i ]['params']['enb_opts'] = 1;
			$res[ $i ]['params']['enb_agent_auto_update'] = 1;
			$res[ $i ]['params']['idle_delay'] = LCS_IDLE_DELAY;	// Seconds
			
			$res[ $i ]['params'] = base64_encode(utilsLcs::serialize( $res[ $i ]['params'] ));
		}
		return $res;
	}
	private function _getExportData($table, $cols, $where = '') {
		$res = dbLcs::get('SELECT '. implode(',', $cols). ' FROM '. $table. ' '. (empty($where) ? '' : 'WHERE '. $where));
		if($table == '@__chat_engines') {
			$res = $this->_prepareExportEngines( $res );
		}
		return $res;
	}
	/**
	 * new usage
	 */
	private function _makeExportQueriesLogic($table, $cols, $where = '') {
		global $wpdb;
		$eol = "\r\n";
		$octoList = $this->_getExportData($table, $cols, $where);
		$valuesArr = array();
		$allKeys = array();
		$uidIndx = 0;
		$idIndx = 0;
		$i = 0;
		foreach($octoList as $octo) {
			$arr = array();
			$addToKeys = empty($allKeys);
			$i = 0;
			foreach($octo as $k => $v) {
				if(!in_array($k, $cols)) continue;
				if($addToKeys) {
					$allKeys[] = $k;
					if($k == 'unique_id') {
						$uidIndx = $i;
					}
					if($k == 'id') {
						$idIndx = $i;
					}
				}
				$arr[] = ''. $wpdb->_real_escape($v). '';
				$i++;
			}
			$valuesArr[] = $arr;
		}
		$out = '';
		//$out .= "\$cols = array('". implode("','", $allKeys). "');". $eol;
		$out .= "\$data = array(". $eol;
		foreach($valuesArr as $row) {
			$uid = str_replace(array('"'), '', $row[ $uidIndx ]);
			$installData = array();
			$isDefChatEngine = ($table == '@__chat_engines' && $row[ $idIndx ] == LCS_DEF_ENGINE_ID);
			foreach($row as $i => $v) {
				$newStr = "'{$allKeys[ $i ]}' => ";
				if($isDefChatEngine && $allKeys[ $i ] == 'id') {
					$newStr .= 'LCS_DEF_ENGINE_ID';
				} else {
					$newStr .= "'{$v}'";
				}
				$installData[] = $newStr;//"'{$allKeys[ $i ]}' => '{$v}'";
			}
			if($isDefChatEngine) {
				$out .= 'LCS_DEF_ENGINE_ID';
			} else {
				$out .= "'$uid'";
			}
			$out .= " => array(". implode(',', $installData). "),". $eol;
		}
		$out .= ");". $eol;
		return $out;
	}
	/**
	 * @see controller::getPermissions();
	 */
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array('welcomePageSaveInfo', 'sendContact', 'addNoticeAction')
			),
		);
	}
}