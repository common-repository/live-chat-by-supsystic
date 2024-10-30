<?php
class chat_enginesModelLcs extends modelLcs {
	private $_showToList = array();
	private $_showPagesList = array();
	private $_showOnList = array();
	
	public function __construct() {
		$this->_setTbl('chat_engines');
	}
	public function createDefaultEngine() {
		$id = $this->insert(array(
			'label' => __('Default Engine', LCS_LANG_CODE),
			'active' => 1,
			'params' => $this->getDefaultParams(),
		));
		if($id) {
			$this->getModel('chat')->setDefaultEngineId( $id );
		}
	}
	public function getDefaultParams() {
		return array(
			'chat_position' => 'bottom_left',
			'opts_attrs' => array(
				//'txt_block_number' => 2,
			),
		);
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
			if(!isset($row['params']['reg_fields']) || empty($row['params']['reg_fields'])) {
				$row['params']['reg_fields'] = array(
					array('label' => __('E-Mail', LCS_LANG_CODE), 'html' => 'text', 'enb' => 1, 'mandatory' => 1, 'name' => 'email', 'html' => 'text'),
					array('label' => __('Name', LCS_LANG_CODE), 'html' => 'text', 'enb' => 1, 'name' => 'name', 'html' => 'text'),
				);
				$row['params']['reg_type'] = 'required';
				$row['params']['reg_event'] = 'before_chat';
			}
			$row['params']['reg_not_mandatory'] = (isset($row['params']['reg_type']) && in_array($row['params']['reg_type'], array('none', 'ask')));
			if(!isset($row['params']['def_avatar']) || empty($row['params']['def_avatar'])) {
				$row['params']['def_avatar'] = $this->getModule()->getModPath(). 'img/avatar-female.png';
			}
			if(!isset($row['params']['idle_delay'])) {
				$row['params']['idle_delay'] = LCS_IDLE_DELAY;
			}
		}
		return $row;
	}
	protected function _dataSave($data, $update = false) {
		if(isset($data['params']))
			$data['params'] = base64_encode(utilsLcs::serialize( $data['params'] ));
		return $data;
	}
	public function save($d = array()) {
		foreach($this->getModel()->getEngineTxtEditors() as $k) {
			if(isset($d['params'][ $k ])) {
				$d['params'][ $k ] = urldecode($d['params'][ $k ]);
			}
		}
		
		$this->getShowOnList();
		$this->getShowToList();
		$this->getShowPagesList();
		
		$d['show_on'] = isset($d['params']['main']['show_on']) ? $this->_showOnList[ $d['params']['main']['show_on'] ]['id'] : 0;
		$d['show_to'] = isset($d['params']['main']['show_to']) ? $this->_showToList[ $d['params']['main']['show_to'] ]['id'] : 0;
		$d['show_pages'] = isset($d['params']['main']['show_pages']) ? $this->_showPagesList[ $d['params']['main']['show_pages'] ]['id'] : 0;
		
		$res = $this->updateById($d);
		if($res) {
			$this->_bindShowToPages( $d );
			dispatcherLcs::doAction('afterChatEngineUpdate', $d);
		}
		return $res;
	}
	public function getById($id) {
		$data = parent::getById($id);
		if($data) {
			$data['show_pages_list'] = frameLcs::_()->getTable('chat_engines_show_pages')->get('*', array('engine_id' => $id));
		}
		return $data;
	}
	private function _bindShowToPages( $d ) {
		$id = (int) $d['id'];
		if($id) {
			frameLcs::_()->getTable('chat_engines_show_pages')->delete(array('engine_id' => $id));
			$insertArr = array();
			if(isset($d['show_pages_list']) && !empty($d['show_pages_list'])) {
				foreach($d['show_pages_list'] as $postId) {
					$insertArr[] = "($id, $postId, 0)";
				}
			}
			if(isset($d['not_show_pages_list']) && !empty($d['not_show_pages_list'])) {
				foreach($d['not_show_pages_list'] as $postId) {
					$insertArr[] = "($id, $postId, 1)";
				}
			}
			if(!empty($insertArr)) {
				dbLcs::query('INSERT INTO @__chat_engines_show_pages (engine_id, post_id, not_show) VALUES '. implode(',', $insertArr));
			}
		}
	}
	public function getShowToList() {
		if(empty($this->_showToList)) {
			$this->_showToList = array(
				'everyone' => array('id' => 1),
				'first_time_visit' => array('id' => 2),
				'for_countries' => array('id' => 3),
				'until_make_action' => array('id' => 4),
			);
		}
		return $this->_showToList;
	}
	public function getShowPagesList() {
		if(empty($this->_showPagesList)) {
			$this->_showPagesList = array(
				'all' => array('id' => 1),
				'show_on_pages' => array('id' => 2),
				'not_show_on_pages' => array('id' => 3),
			);
		}
		return $this->_showPagesList;
	}
	public function getShowOnList() {
		if(empty($this->_showOnList)) {
			$this->_showOnList = array(
				'page_load' => array('id' => 1),
				'click_on_page' => array('id' => 2),
				'click_on_element' => array('id' => 3),
				'scroll_window' => array('id' => 4),
				'on_exit' => array('id' => 5),
				'page_bottom' => array('id' => 6),
				'after_inactive' => array('id' => 7),
				'after_comment' => array('id' => 8),
				'after_checkout' => array('id' => 9),
				'link_follow' => array('id' => 10),
			);
		}
		return $this->_showPagesList;
	}
	public function clearCachedStats($id) {
		$tbl = $this->getTbl();
		$id = (int) $id;
		return dbLcs::query("UPDATE @__$tbl SET `views` = 0, `unique_views` = 0, `actions` = 0 WHERE `id` = $id");
	}
	public function addCachedStat($id, $statColumn) {
		$tbl = $this->getTbl();
		$id = (int) $id;
		return dbLcs::query("UPDATE @__$tbl SET `$statColumn` = `$statColumn` + 1 WHERE `id` = $id");
	}
	public function addViewed($id) {
		return $this->addCachedStat($id, 'views');
	}
	public function addUniqueViewed($id) {
		return $this->addCachedStat($id, 'unique_views');
	}
	public function addActionDone($id) {
		return $this->addCachedStat($id, 'actions');
	}
	public function switchActive($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : false;
		$d['active'] = isset($d['active']) ? (int) $d['active'] : false;
		if($d['id'] !== false && $d['active'] !== false) {
			return $this->updateById(array('active' => $d['active']), $d['id']);
		} else
			$this->pushError(__('Hmm, something is missing here?', LCS_LANG_CODE));
		return false;
	}
}
