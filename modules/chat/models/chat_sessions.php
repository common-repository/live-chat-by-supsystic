<?php
class chat_sessionsModelLcs extends modelLcs {
	private $_statuses = array();
	private $_light = false;
	
	public function __construct() {
		$this->_setTbl('chat_sessions');
	}
	protected function _afterGetFromTbl($row) {
		if(isset($row['status_id'])) {
			$statusData = $this->getStatusById( $row['status_id'] );
			$row['status_code'] = $statusData['code'];
			$row['status_label'] = $statusData['label'];
		}
		if(!$this->_light) {
			if(empty($row['user_id'])) {
				$row['user'] = false;
			} else {
				$row['user'] = $this->getModel('chat_users')->getById( $row['user_id'] );
			}
			if(empty($row['agent_id'])) {
				$row['agent'] = false;
			} else {
				$row['agent'] = $this->getModel('chat_users')->getById( $row['agent_id'] );
			}
		}
		return $row;
	}
	protected function _dataSave($data, $update = false) {
		return $data;
	}
	public function create($d = array()) {
		$data = array_merge(array(
			'status_id' => $this->getStatusId('pending'),
		), $d);
		return $this->insert($data);
	}
	public function getStatuses() {
		if(empty($this->_statuses)) {
			$this->_statuses = array(
				LCS_SES_PENDING => array('id' => 0, 'label' => __('Pending', LCS_LANG_CODE)),
				LCS_SES_IN_PROGRESS => array('id' => 1, 'label' => __('In Progress', LCS_LANG_CODE)),
				LCS_SES_COMPLETE => array('id' => 2, 'label' => __('Complete', LCS_LANG_CODE)),
				LCS_SES_USER_GONE => array('id' => 3, 'label' => __('User Gone', LCS_LANG_CODE)),
				LCS_SES_CANCELLED => array('id' => 4, 'label' => __('Cancelled', LCS_LANG_CODE)),
			);
		}
		return $this->_statuses;
	}
	public function getStatusId($code) {
		$this->getStatuses();
		return isset($this->_statuses[ $code ]) ? $this->_statuses[ $code ]['id'] : false;
	}
	public function getStatusById($id) {
		$this->getStatuses();
		static $statusesByIds = array();
		if(empty($statusesByIds)) {
			foreach($this->_statuses as $code => $s) {
				$statusesByIds[ $s['id'] ] = array_merge(array(
					'code' => $code,
				), $s);
			}
		}
		return isset($statusesByIds[ $id ]) ? $statusesByIds[ $id ] : false;
	}
	public function checkStart( $sessionId ) {
		$session = $this->getById($sessionId);
		if($session['status_code'] != 'in_progress') {
			$this->update(array(
				'status_id' => $this->getStatusId('in_progress'),
				'agent_id' => $this->getModel('chat_users')->getCurrentAgentId(),
			), array(
				'id' => $sessionId,
			));
		}
	}
	public function startSession( $d = array() ) {
		$prevSid = $this->getModule()->getSessionId();
		if($prevSid) {
			//TODO: Uncomment this to use prev. session if it is avalable
			$this->update(array(
				'status_id' => $this->getStatusId('pending'),
			), array(
				'id' => $prevSid,
			));
			return $prevSid;
		}
		$url = isset($d['url']) ? htmlspecialchars(trim($d['url'])) : '';
		$sid = $this->create(array(
			'engine_id' => $d['engine_id'],
			'user_id' => isset($d['user_id']) ? (int) $d['user_id'] : 0,
			'url' => $url,
			'ip' => utilsLcs::getIP(),
		));
		if($sid) {
			$this->getModule()->setSessionId($sid);
			$this->checkSendNewNotification( $d, $sid );
			frameLcs::_()->getModule('statistics')->getModel()->add(array(
				'id' => $d['engine_id'],
				'type' => 'session_start',
			));
			return $sid;
		}
		return false;
	}
	public function checkSendNewNotification($d, $sid) {
		$engine = $this->getModel('chat_engines')->getById( $d['engine_id'] );
		$adminEmail = get_bloginfo('admin_email');
		if(!isset($engine['params']['notif_new_email'])) {
			$sendTo = $adminEmail;	// For case when it was just not existed before, but notification should still come
		} else {
			$sendTo = isset($engine['params']['notif_new_email']) && !empty($engine['params']['notif_new_email'])
				? trim($engine['params']['notif_new_email'])
				: false;
		}
		if(!empty($sendTo)) {
			$blogName = wp_specialchars_decode(get_bloginfo('name'));
			$defSubject = __('New chat started notification', LCS_LANG_CODE);
			$emailSubject = empty($blogName) 
				? $defSubject
				: sprintf(__('New chat started on %s', LCS_LANG_CODE), $blogName);
			$defContent = __('New chat was started on your site <a href="[siteurl]">[sitename]</a>.<br /><b>User information:</b><br />[user_data]<br /><b>Session information:</b><br />[session_data]<br />', LCS_LANG_CODE);
			$emailContent = isset($engine['params']['notif_new_message']) && !empty($engine['params']['notif_new_message'])
				? $engine['params']['notif_new_message']
				: $defContent;
			
			$userDataArr = array();
			if(isset($d['user_id']) 
				&& !empty($d['user_id']) 
				&& ($user = $this->getModel('chat_users')->getById($d['user_id']))
			) {
				if(isset($engine['params']['reg_fields'])
					&& !empty($engine['params']['reg_fields'])
				) {
					foreach($engine['params']['reg_fields'] as $k => $f) {
						if(isset($user[ $k ])) {
							$userDataArr[] = sprintf($f['label']. ': %s', $user[ $k ]);
						}
					}
				}
			}
			$sessionDataArr = array();
			$sessionDataKeys = array(
				'status_label' => __('Status', LCS_LANG_CODE),
				'ip' => __('IP', LCS_LANG_CODE),
				'url' => __('URL', LCS_LANG_CODE),
				'date_created' => __('Date', LCS_LANG_CODE),
			);
			$session = $this->getById( $sid );
			if($session) {
				foreach($sessionDataKeys as $k => $v) {
					$sessionDataArr[] = sprintf($v. ': %s', $session[ $k ]);
				}
			}
			$replaceVariables = array(
				'sitename' => $blogName,
				'siteurl' => get_bloginfo('wpurl'),
				'user_data' => empty($userDataArr) ? __('No user data', LCS_LANG_CODE) : implode('<br />', $userDataArr),
				'session_data' => empty($sessionDataArr) ? __('No session data available', LCS_LANG_CODE) : implode('<br />', $sessionDataArr),
			);
			foreach($replaceVariables as $k => $v) {
				$emailSubject = str_replace('['. $k. ']', $v, $emailSubject);
				$emailContent = str_replace('['. $k. ']', $v, $emailContent);
			}
			frameLcs::_()->getModule('mail')->send($sendTo,
				$emailSubject,
				$emailContent,
				$blogName,
				$adminEmail,
				$blogName,
				$adminEmail);
		}
	}
	/*
	 * If session was pending or in_progress
	 * and don't have any message from user (not agent) for more then 2 hours, 
	 * or if last user message in session was sent more then 2 hours ago - 
	 * then we will assign this session status "user_gone"
	 */
	public function checkUserGone() {
		$lastUsersGoneCheck = (int) frameLcs::_()->getModule('options')->get('last_chat_user_gone_check');
		$time = time();
		$offlineUserTime = 2 * HOUR_IN_SECONDS;	// After 2 hours user will be gone
		if(!$lastUsersGoneCheck || ($lastUsersGoneCheck + $offlineUserTime) <= $time) {
			frameLcs::_()->getModule('options')->getModel()->save('last_chat_user_gone_check', $time);
			$this->_light = true;
			$canBeInStatuses = array($this->getStatusId('pending'), $this->getStatusId('in_progress'));
			$sessions = $this->setSelectFields('id, user_id, UNIX_TIMESTAMP(date_created) AS timestamp_created')
				->setWhere('status_id IN ('. implode(',', $canBeInStatuses). ')')->getFromTbl();
			if(!empty($sessions)) {
				$msgModel = $this->getModel('chat_messages');
				$dbCurrentTimestamp = dbLcs::getCurrentTimestamp();
				$sessionsUserGone = array();
				foreach($sessions as $s) {
					$lastUserMsg = $msgModel->setSelectFields('id, UNIX_TIMESTAMP(date_created) AS timestamp_created')->setWhere(array(
						'session_id' => $s['id'],
						'user_id' => $s['user_id'],
					))->setOrderBy('id')->setSortOrder('DESC')->setLimit(1)->getFromTbl(array('return' => 'row'));
					$compareDate = (int) (empty($lastUserMsg) ? $s['timestamp_created'] : $lastUserMsg['timestamp_created']);
					if($compareDate + $offlineUserTime <= $dbCurrentTimestamp) {
						$sessionsUserGone[] = $s['id'];
					}

				}
				if(!empty($sessionsUserGone)) {
					$this->update(array(
						'status_id' => $this->getStatusId('user_gone'),
					), 'id IN ('. implode(',', $sessionsUserGone). ')');
				}
			}
			$this->_light = false;
		}
	}
	public function setSessionStatus($d = array()) {
		$d['session_id'] = isset($d['session_id']) ? (int) $d['session_id'] : false;
		$d['status_id'] = isset($d['status_id']) ? (int) $d['status_id'] : false;
		if($d['session_id'] !== false && $d['status_id'] !== false) {
			return $this->updateById(array(
				'status_id' => $d['status_id'],
			), $d['session_id']);
		} else
			$this->pushError(__('Something is missing here', LCS_LANG_CODE));
		return false;
	}
	public function checkUserReneview( $sessionId ) {
		$session = $this->getById( $sessionId );
		if(!in_array($session['status_code'], array('pending', 'in_progress'))) {
			$this->update(array(
				'status_id' => $this->getStatusId('pending'),
			), array(
				'id' => $sessionId,
			));
		}
	}
}
