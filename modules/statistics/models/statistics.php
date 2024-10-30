<?php
class statisticsModelLcs extends modelLcs {
	public function __construct() {
		$this->_setTbl('statistics');
	}
	public function add($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		$d['type'] = isset($d['type']) ? $d['type'] : '';
		if(!empty($d['id']) && !empty($d['type'])) {
			$typeId = $this->getModule()->getTypeIdByCode( $d['type'] );
			$isUnique = 0;
			if(isset($d['is_unique']) && !empty($d['is_unique'])) {
				$isUnique = (int) 1;	// This is realy cool :)
			}
			$enginesModel = frameLcs::_()->getModule('chat')->getModel('chat_engines');
			if(in_array($d['type'], array('show'))) {
				$enginesModel->addViewed( $d['id'] );
				if($isUnique) {
					$enginesModel->addUniqueViewed( $d['id'] );
				}
			} else {	// Any action count here
				$enginesModel->addActionDone( $d['id'] );
			}
			return $this->insert(array(
				'engine_id' => $d['id'],
				'type' => $typeId,
				'is_unique' => $isUnique,
			));
		} else
			$this->pushError(__('Send me some info, pls', LCS_LANG_CODE));
		return false;
	}
	/**
	 * Get list for chat engine
	 * @param numeric $id Engine ID
	 * @param array $params Additional selection params, $params = array('type' => '')
	 * @return array List of statistics data
	 */
	public function getForEngine($id, $params = array()) {
		$where = array('engine_id' => $id);
		$typeId = isset($params['type']) ? $params['type'] : 0;
		if($typeId && !is_numeric($typeId)) {
			$typeId = $this->getModule()->getTypeIdByCode( $typeId );
		}
		if($typeId) {
			$where['type'] = $typeId;
		}
		$group = isset($params['group']) ? $params['group'] : 'day';
		$sqlDateFormat = $this->_getDateFormatForForGroup( $group );
		
		return $this->setSelectFields('COUNT(*) AS total_requests, SUM(is_unique) AS unique_requests, '. $sqlDateFormat. ' AS date')
				->groupBy('date')
				->setOrderBy('date')
				->setSortOrder('DESC')
				->setWhere($where)
				->getFromTbl();
	}
	public function getMessagesStats( $params = array() ) {
		$model = frameLcs::_()->getModule('chat')->getModel('chat_messages');
		$group = isset($params['group']) ? $params['group'] : 'day';
		$sqlDateFormat = $this->_getDateFormatForForGroup( $group );
		
		return $model->setSelectFields('COUNT(*) AS total_requests, '. $sqlDateFormat. ' AS date')
				->groupBy('date')
				->setOrderBy('date')
				->setSortOrder('DESC')
				->getFromTbl();
	}
	/**
	 * Same as getMessagesStats() methd - just return it in format, like getAllForEngineId() method do for other stats
	 */
	public function getMessagesStatsForGraph( $params = array() ) {
		return array(array(
			'code' => 'total_msgs',
			'label' => __('Messages', LCS_LANG_CODE),
			'points' => $this->getMessagesStats( $params ),
		));
	}
	private function _getDateFormatForForGroup( $group ) {
		$sqlDateFormat = '';
		switch($group) {
			case 'hour':
				$sqlDateFormat = 'DATE_FORMAT(date_created, "%m-%d-%Y %H:00")';
				break;
			case 'week':
				$sqlDateFormat = 'DATE_FORMAT(DATE_SUB(date_created, INTERVAL DAYOFWEEK(date_created)-1 DAY), "%m-%d-%Y")';
				break;
			case 'month':
				$sqlDateFormat = 'DATE_FORMAT(date_created, "%m-01-%Y")';
				break;
			case 'day':
			default:
				$sqlDateFormat = 'DATE_FORMAT(date_created, "%m-%d-%Y")';
				break;
		}
		return $sqlDateFormat;
	}
	public function getSmActionForPopup($popupId) {
		$where = array('popup_id' => $popupId, 'additionalCondition' => ' sm_id != 0 ');
		$data = $this->setSelectFields('COUNT(*) AS total_requests, sm_id')
				->groupBy('sm_id')
				->setWhere($where)
				->getFromTbl();
		if(!empty($data)) {
			foreach($data as $i => $row) {
				$data[ $i ]['sm_type'] = frameLcs::_()->getModule('sm')->getTypeById( $row['sm_id'] );
			}
		}
		return $data;
	}
	public function clearForEngine($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		if($d['id']) {
			// This was from prev. PopUp plugin
			//frameLcs::_()->getModule('chat')->getModel()->clearCachedStats( $d['id'] );
			return $this->delete(array('engine_id' => $d['id']));
		} else
			$this->pushError(__('Invalid ID', LCS_LANG_CODE));
		return false;
	}
	public function getAllForEngineId($id, $params = array()) {
		$allTypes = $this->getModule()->getTypes();
		$allStats = array();
		$haveData = false;
		$i = 0;
		foreach($allTypes as $typeCode => $type) {
			$params['type'] = $type['id'];
			$allStats[ $i ] = $type;
			$allStats[ $i ]['code'] = $typeCode;
			$allStats[ $i ]['points'] = $this->getForEngine($id, $params);
			if(!empty($allStats[ $i ]['points'])) {
				$haveData = true;
			}
			$i++;
		}
		return $haveData ? $allStats : false;
	}
	public function getUpdatedStats($d = array()) {
		$id = isset($d['id']) ? (int) $d['id'] : 0;
		if($id) {
			$popup = frameLcs::_()->getModule('popup')->getModel()->getById( $id );
			$params = array();
			if(isset($d['group']))
				$params['group'] = $d['group'];
			$allStats = $this->getAllForEngineId($id, $params);
			$allStats = dispatcherLcs::applyFilters('popupStatsAdminData', $allStats, $popup);
			return $allStats;
		} else
			$this->pushError (__('Invalid ID', LCS_LANG_CODE));
		return false;
	}
	public function getPreparedStats($d = array()) {
		$stats = $this->getUpdatedStats( $d );
		if($stats) {
			$dataToDate = array();
			foreach($stats as $i => $stat) {
				if(isset($stat['points']) && !empty($stat['points'])) {
					foreach($stat['points'] as $j => $point) {
						$date = $point['date'];
						$currentData = array(
							'date' => $date,
							'views' =>  0,
							'unique_requests' => 0,
							'actions' => 0,
							'conversion' => 0,
						);
						if(in_array($stat['code'], array('show'))) {
							$currentData['views'] = (int)( $point['total_requests'] );
						} else {
							$currentData['actions'] = (int)( $point['total_requests'] );
						}
						$uniqueRequests = (int)( $point['unique_requests'] );
						if($uniqueRequests) {
							$currentData['unique_requests'] = $uniqueRequests;
						}
						if(isset($dataToDate[ $date ])) {
							$currentData['views'] += $dataToDate[ $date ]['views'];
							$currentData['actions'] += $dataToDate[ $date ]['actions'];
							$currentData['unique_requests'] += $dataToDate[ $date ]['unique_requests'];
						}
						$dataToDate[ $date ] = $currentData;
					}
				}
			}
			return $dataToDate;
		} else
			$this->pushError (__('No data found', LCS_LANG_CODE));
		return false;
	}
}