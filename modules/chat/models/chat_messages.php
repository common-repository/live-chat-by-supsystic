<?php
class chat_messagesModelLcs extends modelLcs {
	public function __construct() {
		$this->_setTbl('chat_messages');
	}
	protected function _afterGetFromTbl($row) {
		if(isset($row['date_created'])) {
			// TODO: Add more flexible and attractive time output here
			$timeStamp = strtotime($row['date_created']);
			$row['date_created_format'] = date('m/d/Y H:i:s', $timeStamp);
		}
		if(isset($row['msg']) && !empty($row['msg'])) {
			// To make correct new lines display. 
			// Make sure it will not break anything: for example - if it will be used for re-saving with <br> tags in it
			$row['msg'] = nl2br( $row['msg'] );
		}
		return $row;
	}
	/*protected function _dataSave($data, $update = false) {
		return $data;
	}*/
	public function save($d = array(), $engine = array()) {
		$email = isset($d['email']) ? trim($d['email']) : '';
		$name = isset($d['name']) ? trim($d['name']) : '';
		if($email && ($user = $this->getBy('email', $email, true))) {
			$res = $user['id'];
		} else {
			$params = array(
				'fields' => isset($d['fields']) ? $d['fields'] : array(),
			);
			$data = array(
				'name' => $name,
				'email' => $email,
				'ip' => utilsLcs::getIP(),
				'active' => 1,
				'position' => $d['position'],
				'params' => $params,
			);
			$res = $this->insert($data);
		}
		if($res) {
			dispatcherLcs::doAction('afterChatUserUpdate', $d);
		}
		return $res;
	}
	public function create($d = array()) {
		return $this->insert($d);
	}
	public function getListForSession( $sessionId = 0 ) {
		if(!$sessionId) {
			$sessionId = $this->getModule()->getSessionId();
		}
		$list = $this->getBy('session_id', $sessionId);
		if($list) {
			$session = $this->getModel('chat_sessions')->getById( $sessionId );
			$userIds = array();
			foreach($list as $l) {
				$userIds[ (int) $l['user_id'] ] = 1;
			}
			$users = $this->getModel('chat_users')->setWhere('id IN ('. implode(',', array_keys($userIds)). ')')->getFromTbl();
			$sessionKeysAllowed = array('id', 'status_code', 'status_label', 'status_id');
			$sessionToSend = array();
			foreach($sessionKeysAllowed as $sKey) {
				$sessionToSend[ $sKey ] = isset($session[ $sKey ]) ? $session[ $sKey ] : false;
			}
			return array(
				'list' => $list, 
				'users' => $users, 
				'session' => $sessionToSend,
			);
		}
		return false;
	}
	public function agentSend($d = array()) {
		$d['session_id'] = (int) $d['session_id'];
		$agentId = $this->getModel('chat_users')->getCurrentAgentId();
		// For some reasons - base user admin can be not created. For this case - let it be created here
		if(!$agentId && frameLcs::_()->getModule('user')->isAdmin()) {
			installerLcs::addBaseUsers();
			$agentId = $this->getModel('chat_users')->getCurrentAgentId();
		}
		$msg = isset($d['msg']) ? trim(esc_html($d['msg'])) : '';
		if($d['session_id'] && $agentId) {
			if(!empty($msg)) {
				$this->getModel('chat_sessions')->checkStart( $d['session_id'] );
				return $this->create(array(
					'session_id' => $d['session_id'],
					'user_id' => $agentId,
					'msg' => $msg,
				));
			} else
				$this->pushError(__('Please enter something', LCS_LANG_CODE));
		} else
			$this->pushError(__('Something is missing here', LCS_LANG_CODE));
		return false;
	}
	public function userSend($d = array(), $forceSkipReg = false) {
		$d['session_id'] = (int) $this->getModule()->getSessionId();
		$userId = $this->getModule()->getCurrentUserId();
		$msg = isset($d['msg']) ? trim(esc_html($d['msg'])) : '';
		$skipRegistration = false;
		if(!$userId && !$forceSkipReg) {
			$engine = $this->getModel('chat_engines')->getById( (int)$d['id'] );
			if($engine && $engine['params']['reg_type'] != 'required') {
				$skipRegistration = true;
			}
		}
		if($d['session_id'] && ($userId || $skipRegistration)) {
			if(!empty($msg)) {
				$this->getModel('chat_sessions')->checkUserReneview( $d['session_id'] );
				return $this->create(array(
					'session_id' => $d['session_id'],
					'user_id' => $userId,
					'msg' => $msg,
				));
			} else
				$this->pushError(__('Please enter something', LCS_LANG_CODE));
		} else
			$this->pushError(__('Something is missing here', LCS_LANG_CODE));
		return false;
	}
	public function addAutoMessage( $engineId, $triggerId ) {
		$trigger = $this->getModel('chat_triggers')->getById($triggerId);
		if($trigger && $trigger['actions']) {
			foreach($trigger['actions'] as $i => $a) {
				if($a['code'] == LCS_AUTO_START && isset($a['enb']) && !empty($a['msg'])) {
					if($this->getModule()->getAutoMsgSent()) {
						return true;	// We don't need to send more then one auto messages per session
					}
					$sessionId = $this->getModule()->getSessionId();
					$agentId = $this->getModel('chat_users')->getBotId();
					$res = $this->create(array(
						'session_id' => $sessionId,
						'user_id' => $agentId,
						'msg' => $a['msg'],
					));
					if($res) {
						$this->getModule()->setAutoMsgSent();
						return $res;
					}
				}
			}
		}
		return false;
	}
}
