<?php
class chatControllerLcs extends controllerLcs {
	protected function _prepareListForTbl($data) {
		if($this->_listModelName == 'chat_sessions') {
			if(!empty($data)) {
				foreach($data as $i => $v) {
					$data[ $i ]['user_email'] = '<a href="#" onclick="lcsShowAgentChatSessionWnd('. $data[ $i ]['id']. '); return false;">'
						. (empty($data[ $i ]['user']) ? $data[ $i ]['ip'] : $data[ $i ]['user']['email'])
						. '&nbsp;<i class="fa fa-comment"></i></a>';
					$data[ $i ]['agent_email'] = $data[ $i ]['agent'] 
						? '<a class="" href="#">'. $data[ $i ]['agent']['email']. '</a>'	
						: __('Not Assigned', LCS_LANG_CODE);
					$data[ $i ]['actions'] = '<a title="'. __('Start conversation', LCS_LANG_CODE). '" href="" class="button" onclick="lcsShowAgentChatSessionWnd('. $data[ $i ]['id']. '); return false;"><i class="fa fa-comment"></i></a>';
				}
			}
		}
		if($this->_listModelName == 'chat_users') {
			if(!empty($data)) {
				foreach($data as $i => $v) {
					$data[ $i ]['name'] = '<a href="#" onclick="lcsEditAgentName(this, '. $data[ $i ]['id']. '); return false;">'
						. '<span class="lcsAgentTblName">'. $data[ $i ]['name']. '</span>'
						. '&nbsp;<i class="fa fa-pencil"></a>';
					$data[ $i ]['active'] = $data[ $i ]['active'] ? '<span class="alert alert-success">'. __('Yes', LCS_LANG_CODE). '</span>' : '<span class="alert alert-danger">'. __('No', LCS_LANG_CODE). '</span>';
				}
			}
		}
		return $data;
	}
	protected function _prepareModelBeforeListSelect( $model ) {
		if($this->_listModelName == 'chat_sessions') {
			$this->getModel('chat_sessions')->checkUserGone();
			$sessionStatus = reqLcs::getVar('session_status');
			if(is_numeric($sessionStatus)) {
				$model->addWhere(array('status_id' => $sessionStatus));
			}
		}
		if($this->_listModelName == 'chat_users' && reqLcs::getVar('user_types') == 'agents') {
			$model->addWhere('position != '. $this->getModel('chat_users')->getPositionId('user'));
		}
		return $model;
	}
	protected function _prepareTextLikeSearch( $v ) {
		$res = '';
		if($this->_listModelName == 'chat_sessions') {
			$res = 'ip LIKE "%'. $v. '%" OR url LIKE "%'. $v. '%"';
			$idsFromMsg = dbLcs::get('SELECT session_id FROM @__chat_messages WHERE msg LIKE "%'. $v. '%" GROUP BY session_id', 'col');
			if(!empty($idsFromMsg)) {
				$res .= ' OR id IN ('. implode(',', $idsFromMsg). ')';
			}
		}
		return $res;
	}
	public function getPreviewHtml() {
		$this->outPreviewHtml();
	}
	public function outPreviewHtml() {
		$chatContent = $this->getView()->generateHtml( $this->getModel()->getCurrentTpl() );
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
		<html><head>'
		. '<meta content="'. get_option('html_type'). '; charset='. get_option('blog_charset'). '" http-equiv="Content-Type">'
		. '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css" />'
		. '<style type="text/css"> 
			html { overflow: visible !important; } 
			.lcsOptsList {
				display: none;
			}
			</style>'
		. '</head>';
		echo '<body>';
		echo $chatContent;
		echo '<body></html>';
		exit();
	}
	public function saveSettings() {
		$res = new responseLcs();
		if($this->getModel()->saveSettings( reqLcs::get('post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function agentChat() {
		echo $this->getView()->generateAgentChatPage( (int) reqLcs::getVar('session_id') );
		exit();
	}
	public function getMsgList() {
		$res = new responseLcs();
		
		reqLcs::startSession();
		
		if(($messages = $this->getModel('chat_messages')->getListForSession( reqLcs::getVar('session_id', 'post') )) !== false) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
			$res->addData('messages', $messages);
		} else
			$res->pushError($this->getModel('chat_messages')->getErrors());
		$res->ajaxExec();
	}
	public function agentSend() {
		$res = new responseLcs();
		
		reqLcs::startSession();
		
		if($this->getModel('chat_messages')->agentSend( reqLcs::get('post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
			$res->addData('messages', $this->getModel('chat_messages')->getListForSession( reqLcs::getVar('session_id', 'post')));
		} else
			$res->pushError($this->getModel('chat_messages')->getErrors());
		$res->ajaxExec();
	}
	public function userSend() {
		$res = new responseLcs();
		
		reqLcs::startSession();
		
		if($this->getModel('chat_messages')->userSend( reqLcs::get('post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
			$res->addData('messages', $this->getModel('chat_messages')->getListForSession());
		} else
			$res->pushError($this->getModel('chat_messages')->getErrors());
		$res->ajaxExec();
	}
	public function startSession() {
		$res = new responseLcs();
		
		reqLcs::startSession();
		
		$data = array(
			'engine_id' => reqLcs::getVar('id', 'post'),
			'url' => reqLcs::getVar('url', 'post'),
			'autostart' => (int) reqLcs::getVar('autostart', 'post'),
			'trigger_id' => (int) reqLcs::getVar('trigger_id', 'post'),
		);
		if(reqLcs::getVar('_supnonce', 'post') != md5(date('m-d-Y'). $data['engine_id']. NONCE_KEY)) {
			die('Hmm, something is wrong here?');
		}
		if(($sid = $this->getModel('chat_sessions')->startSession( $data ))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
			$res->addData('session_id', $sid);
			if($data['autostart'] 
				&& $data['trigger_id'] 
				&& $this->getModel('chat_messages')->addAutoMessage( $data['engine_id'], $data['trigger_id'] )
			) {
				$res->addData('messages', $this->getModel('chat_messages')->getListForSession());
			}
		} else
			$res->pushError($this->getModel('chat_sessions')->getErrors());
		$res->ajaxExec();
	}
	public function switchActive() {
		$res = new responseLcs();
		if(($sid = $this->getModel('chat_engines')->switchActive( reqLcs::get('post') ))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_engines')->getErrors());
		$res->ajaxExec();
	}
	public function setSessionStatus() {
		$res = new responseLcs();
		if(($sid = $this->getModel('chat_sessions')->setSessionStatus( reqLcs::get('post') ))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_sessions')->getErrors());
		$res->ajaxExec();
	}
	public function saveTrigger() {
		$res = new responseLcs();
		if(($tid = $this->getModel('chat_triggers')->save( reqLcs::getVar('trigger', 'post') ))) {
			$res->addData('trigger', $this->getModel('chat_triggers')->getById($tid));
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_triggers')->getErrors());
		$res->ajaxExec();
	}
	public function removeTrigger() {
		$res = new responseLcs();
		if($this->getModel('chat_triggers')->removeGroup( (int) reqLcs::getVar('id', 'post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_triggers')->getErrors());
		$res->ajaxExec();
	}
	public function toggleTrigger() {
		$res = new responseLcs();
		if($this->getModel('chat_triggers')->toggleTrigger( (int) reqLcs::getVar('id', 'post'), (int) reqLcs::getVar('active', 'post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_triggers')->getErrors());
		$res->ajaxExec();
	}
	public function updateCode() {
		$res = new responseLcs();
		if(($newContent = $this->getModel('chat_templates')->updateCode( reqLcs::get('post') ))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
			$res->addData('new_content', $newContent);
		} else
			$res->pushError($this->getModel('chat_templates')->getErrors());
		$res->ajaxExec();
	}
	public function saveAgentName() {
		$res = new responseLcs();
		if($this->getModel('chat_users')->saveAgentName( reqLcs::get('post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat_users')->getErrors());
		$res->ajaxExec();
	}
	public function sendChatToEmail() {
		$res = new responseLcs();
		if($this->getModel('chat')->sendChatToEmail( reqLcs::get('post') )) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel('chat')->getErrors());
		$res->ajaxExec();
	}
	public function changeTpl() {
		$res = new responseLcs();
		if($this->getModel('chat_templates')->changeTpl(reqLcs::get('post'))) {
			$res->addMessage(__('Updating chat settings page...', LCS_LANG_CODE));
		} else
			$res->pushError ($this->getModel('chat_templates')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array('getTriggersListForTbl', 'getPreviewHtml', 'saveSettings', 'removeGroup', 
					'switchActive', 'saveTrigger', 'removeTrigger', 'toggleTrigger', 'updateCode', 'saveAgentName',
					'changeTpl'),
				LCS_AGENT => array('getListForTbl', 'agentChat', 'agentSend', 'setSessionStatus'),
			),
		);
	}
}

