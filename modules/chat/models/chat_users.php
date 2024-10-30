<?php
class chat_usersModelLcs extends modelLcs {
	private $_positions = array();
	
	public function __construct() {
		$this->_setTbl('chat_users');
	}
	protected function _prepareParamsAfterDb($params) {
		if(is_array($params)) {
			foreach($params as $k => $v) {
				$params[ $k ] = $this->_prepareParamsAfterDb( $v ); 
			}
		} else
			$params = stripslashes ($params);
		return $params;
	}
	protected function _afterGetFromTbl($row) {
		if(isset($row['params'])) {
			$row['params'] = $this->_prepareParamsAfterDb( utilsLcs::unserialize( base64_decode($row['params']) ) );
		}
		$position = $this->getPositionById( $row['position'] );
		$row['position_code'] = $position['code'];
		$row['position_label'] = $position['label'];
		if(!in_array($row['position_code'], array(LCS_USER)) && isset($row['email']) && !empty($row['email'])) {
			$row['avatar'] = $this->extractImgUrl(get_avatar($row['email'], 300, $this->getModule()->getModPath(). 'img/avatar-female.png'));	// Let it be 300 for now
		}
		
		return $row;
	}
	public function extractImgUrl($imgHtml) {
		preg_match("/src='(.*?)'/i", $imgHtml, $matches);
		return $matches[1];
	}
	protected function _dataSave($data, $update = false) {
		if(isset($data['params']))
			$data['params'] = base64_encode(utilsLcs::serialize( $data['params'] ));
		return $data;
	}
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
	public function getPositions() {
		if(empty($this->_positions)) {
			$this->_positions = array(
				LCS_USER => array('id' => 0, 'label' => __('User', LCS_LANG_CODE)),
				LCS_ADMIN => array('id' => 1, 'label' => __('Admin', LCS_LANG_CODE)),
				LCS_AGENT => array('id' => 2, 'label' => __('Agent', LCS_LANG_CODE)),
				LCS_BOT => array('id' => 3, 'label' => __('Bot', LCS_LANG_CODE)),
			);
		}
		return $this->_positions;
	}
	public function getPositionId($code) {
		$this->getPositions();
		return isset($this->_positions[ $code ]) ? $this->_positions[ $code ]['id'] : false;
	}
	public function getPositionById($id) {
		$this->getPositions();
		static $positionsByIds = array();
		if(empty($positionsByIds)) {
			foreach($this->_positions as $code => $s) {
				$positionsByIds[ $s['id'] ] = array_merge(array(
					'code' => $code,
				), $s);
			}
		}
		return isset($positionsByIds[ $id ]) ? $positionsByIds[ $id ] : false;
	}
	public function getCurrentAgent() {
		$fromSession = reqLcs::getVar('lcs_agent', 'session');
		if($fromSession)
			return $fromSession;
		$user = frameLcs::_()->getModule('user')->getCurrent();
		if($user) {
			$agent = $this->getBy('wp_id', $user->ID, true);
			if($agent) {
				reqLcs::setVar('lcs_agent', $agent, 'session');
				return $agent;
			}
		}
		return false;
	}
	public function getCurrentAgentId() {
		$agent = $this->getCurrentAgent();
		return $agent ? $agent['id'] : false;
	}
	public function getBotId() {
		return (int) $this->setWhere(array(
			'position' => $this->getPositionId( LCS_BOT )
		))->setSelectFields('id')->getFromTbl(array('return' => 'one'));
	}
	public function getBotUser() {
		return $this->setWhere(array(
			'position' => $this->getPositionId( LCS_BOT )
		))->getFromTbl(array('return' => 'row'));
	}
	public function saveAgentName($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : false;
		$d['name'] = isset($d['name']) ? trim($d['name']) : false;
		if(!empty($d['id'])) {
			if(!empty($d['id'])) {
				return $this->updateById(array(
					'name' => $d['name'],
				), $d['id']);
			} else
				$this->pushError(__('Name can not be empty', LCS_LANG_CODE), 'name');
		} else
			$this->pushError(__('Something is missing here', LCS_LANG_CODE));
		return false;
	}
}
