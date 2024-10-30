<?php
class chat_triggersModelLcs extends modelLcs {
	private $_types = array();
	private $_equals = array();
	private $_actions = array();
	
	private $_linksReplacement = array();
	
	public function __construct() {
		$this->_setTbl('chat_triggers');
	}
	public function getTypes() {
		if(empty($this->_types)) {
			$this->_types = array(
				1 => array('label' => __('Agent Online', LCS_LANG_CODE), 'equals' => array(LCS_IS_TRUE, LCS_IS_FALSE), 'code' => LCS_AGENT_ONLINE, 'val_type' => LCS_BOOL),
				2 => array('label' => __('Pages / Posts', LCS_LANG_CODE), 'equals' => array(LCS_EQUAL, LCS_NOT_EQUAL), 'code' => LCS_PAGES_POSTS, 'val_type' => LCS_ARRAY),
				3 => array('label' => __('For Countries', LCS_LANG_CODE), 'equals' => array(LCS_EQUAL, LCS_NOT_EQUAL), 'code' => LCS_COUNTRY, 'val_type' => LCS_ARRAY),
				4 => array('label' => __('Hour of the Day', LCS_LANG_CODE), 'equals' => array(LCS_EQUAL, LCS_NOT_EQUAL, LCS_MORE_THEN, LCS_LESS_THEN), 'code' => LCS_DAY_HOUR, 'val_type' => LCS_INT),
				5 => array('label' => __('Day of the Week', LCS_LANG_CODE), 'equals' => array(LCS_EQUAL, LCS_NOT_EQUAL), 'code' => LCS_WEEK_DAY, 'val_type' => LCS_ARRAY),
				6 => array('label' => __('URL', LCS_LANG_CODE), 'equals' => array(LCS_EQUAL, LCS_NOT_EQUAL, LCS_LIKE, LCS_NOT_LIKE), 'code' => LCS_PAGE_URL, 'val_type' => LCS_STRING),
				7 => array('label' => __('Time spent on Page', LCS_LANG_CODE), 'equals' => array(LCS_MORE_THEN), 'code' => LCS_TIME_ON_PAGE, 'val_type' => LCS_ARRAY),
				8 => array('label' => __('On Exit', LCS_LANG_CODE), 'equals' => array(LCS_IS_TRUE), 'code' => LCS_ON_EXIT, 'val_type' => LCS_BOOL, 'is_pro' => true),
			);
		}
		return $this->_types;
	}
	public function getEquals() {
		if(empty($this->_equals)) {
			$this->_equals = array(
				1 => array('label' => __('More then', LCS_LANG_CODE), 'code' => LCS_MORE_THEN),
				2 => array('label' => __('Less then', LCS_LANG_CODE), 'code' => LCS_LESS_THEN),
				3 => array('label' => __('Equal', LCS_LANG_CODE), 'code' => LCS_EQUAL),
				4 => array('label' => __('Not equal', LCS_LANG_CODE), 'code' => LCS_NOT_EQUAL),
				5 => array('label' => __('Like', LCS_LANG_CODE), 'code' => LCS_LIKE),
				6 => array('label' => __('Not like', LCS_LANG_CODE), 'code' => LCS_NOT_LIKE),
				7 => array('label' => __('Yes', LCS_LANG_CODE), 'code' => LCS_IS_TRUE),
				8 => array('label' => __('No', LCS_LANG_CODE), 'code' => LCS_IS_FALSE),
			);
		}
		return $this->_equals;
	}
	public function getActions() {
		if(empty($this->_actions)) {
			$this->_actions = array(
				//1 => array('label' => __('Show chat', LCS_LANG_CODE), 'code' => LCS_SHOW_CHAT),
				1 => array('label' => __('Show eye catcher', LCS_LANG_CODE), 'code' => LCS_SHOW_EAE_CATCH),
				2 => array('label' => __('Auto start chat', LCS_LANG_CODE), 'code' => LCS_AUTO_START),
				3 => array('label' => __('Auto open chat', LCS_LANG_CODE), 'code' => LCS_AUTO_OPEN),
			);
		}
		return $this->_actions;
	}
	protected function _afterGetFromTbl($row) {
		$row['conditions'] = $this->getModel('chat_triggers_conditions')->getBy('trigger_id', $row['id']);
		if(!empty($row['conditions'])) {
			$this->getTypes();
			$this->getEquals();
			foreach($row['conditions'] as $i => $c) {
				$typeId = $c['type'];
				$row['conditions'][ $i ]['value'] = $this->_convertConditionVal($c['value'], $this->_types[ $typeId ]['val_type'], true);
				$row['conditions'][ $i ]['type_code'] = $this->_types[ $typeId ]['code'];
				$row['conditions'][ $i ]['equal_code'] = $this->_equals[ $c['equal'] ]['code'];
			}
		}
		$row['actions'] = empty($row['actions']) ? array() : utilsLcs::unserialize(base64_decode($row['actions']));
		$row = $this->_afterDbReplace($row);
		return $row;
	}
	protected function _dataSave($data, $update = false) {
		$data = $this->_beforeDbReplace($data);
		if(isset($data['actions']))
			$data['actions'] = !empty($data['actions']) ? base64_encode(utilsLcs::serialize($data['actions'])) : '';
		return $data;
	}
	public function save($d = array()) {
		$d['label'] = isset($d['label']) ? trim($d['label']) : false;
		$d['actions'] = isset($d['actions']) ? $d['actions'] : false;
		if(!empty($d['label'])) {
			$actionSelected = false;
			if(!empty($d['actions'])) {
				foreach($d['actions'] as $a) {
					if(isset($a['enb']) && $a['enb']) {
						$actionSelected = true;	// Check for at least one action is selected
						break;
					}
				}
			}
			if($actionSelected) {
				$id = isset($d['id']) ? (int) $d['id'] : 0;
				$update = $d['id'] ? true : false;

				$res = $update ? $this->updateById($d, $id) : $this->insert($d);
				if($res) {
					if(!$update) {
						$id = $res;
					} else {
						$this->_unbindConditions( $id );
					}
					if(isset($d['conditions']) && !empty($d['conditions'])) {
						$this->_bindConditions($id, $d['conditions']);
					}
					return $id;
				}
			} else 
				$this->pushError (__('Please select at least one action for trigger', LCS_LANG_CODE), 'trigger[actions]');
		} else
			$this->pushError (__('Please enter trigger name', LCS_LANG_CODE), 'trigger[label]');
		return false;
	}
	private function _unbindConditions($id) {
		return $this->getModel('chat_triggers_conditions')->delete(array('trigger_id' => $id));
	}
	private function _bindConditions($id, $conditions) {
		$this->getTypes();
		$valuesArr = array();
		foreach($conditions as $c) {
			$typeId = $c['type'];
			$value = $this->_convertConditionVal((isset($c['value']) ? $c['value'] : ''), $this->_types[ $typeId ]['val_type']);
			$valuesArr[] = '('. $id. ', '. $typeId. ', '. $c['equal']. ', "'. $value. '")';
		}
		return dbLcs::query('INSERT INTO @__chat_triggers_conditions (trigger_id, type, equal, value) VALUES '. implode(',', $valuesArr));
	}
	private function _convertConditionVal($val, $valType, $fromDb = false) {
		switch($valType) {
			case LCS_ARRAY:
				$val = $fromDb 
					? utilsLcs::unserialize(base64_decode($val)) 
					: base64_encode(utilsLcs::serialize($val));
				break;
			case LCS_INT:
				$val = (int) $val;
				break;
		}
		return $val;
	}
	protected function _afterRemove($ids) {
		if(!is_array($ids))
			$ids = array( $ids );
		$this->getModel('chat_triggers_conditions')->removeGroup($ids, 'trigger_id');
	}
	private function _getLinksReplacement() {
		if(empty($this->_linksReplacement)) {
			$this->_linksReplacement = array(
				'modUrl' => array('url' => $this->getModule()->getModPath(), 'key' => 'LCS_MOD_URL'),
				'siteUrl' => array('url' => LCS_SITE_URL, 'key' => 'LCS_SITE_URL'),
				'assetsUrl' => array('url' => $this->getModule()->getAssetsUrl(), 'key' => 'LCS_ASSETS_URL'),
				'oldAssets' => array('url' => $this->getModule()->getOldAssetsUrl(), 'key' => 'LCS_OLD_ASSETS_URL'),
			);
		}
		return $this->_linksReplacement;
	}
	protected function _beforeDbReplace($data) {
		static $replaceFrom, $replaceTo;
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$data[ $k ] = $this->_beforeDbReplace($v);
			}
		} else {
			if(!$replaceFrom) {
				$this->_getLinksReplacement();
				foreach($this->_linksReplacement as $k => $rData) {
					if($k == 'oldAssets') {	// Replace old assets urls - to new one
						$replaceFrom[] = $rData['url'];
						$replaceTo[] = '['. $this->_linksReplacement['assetsUrl']['key']. ']';
					} else {
						$replaceFrom[] = $rData['url'];
						$replaceTo[] = '['. $rData['key']. ']';
					}
				}
			}
			$data = str_replace($replaceFrom, $replaceTo, $data);
		}
		return $data;
	}
	protected function _afterDbReplace($data) {
		static $replaceFrom, $replaceTo;
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$data[ $k ] = $this->_afterDbReplace($v);
			}
		} else {
			if(!$replaceFrom) {
				$this->_getLinksReplacement();
				foreach($this->_linksReplacement as $k => $rData) {
					$replaceFrom[] = '['. $rData['key']. ']';
					$replaceTo[] = $rData['url'];
				}
			}
			$data = str_replace($replaceFrom, $replaceTo, $data);
		}
		return $data;
	}
	public function toggleTrigger($id, $active) {
		return $this->updateById(array(
			'active' => !$active,
		), $id);
	}
}
