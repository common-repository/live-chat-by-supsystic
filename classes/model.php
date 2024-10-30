<?php
abstract class modelLcs extends baseObjectLcs {
    protected $_data = array();
	protected $_code = '';
    
	protected $_orderBy = '';
	protected $_sortOrder = '';
	protected $_groupBy = '';
	protected $_limit = '';
	protected $_where = array();
	protected $_stringWhere = '';
	protected $_selectFields = '*';
	protected $_tbl = '';
	protected $_lastGetCount = 0;
	protected $_idField = 'id';
	
    /*public function init() {

    }
    public function get($d = array()) {

    }
    public function put($d = array()) {

    }
    public function post($d = array()) {

    }
    public function delete($d = array()) {

    }
    public function store($d = array()) {
        
    }*/
	public function setCode($code) {
        $this->_code = $code;
    }
    public function getCode() {
        return $this->_code;
    }
	public function getModule() {
		return frameLcs::_()->getModule( $this->_code );
	}
	
	protected function _setTbl($tbl) {
		$this->_tbl = $tbl;
	}	
	public function setOrderBy($orderBy) {
		$this->_orderBy = $orderBy;
		return $this;
	}
	/**
	 * ASC, DESC
	 */
	public function setSortOrder($sortOrder) {
		$this->_sortOrder = $sortOrder;
		return $this;
	}
	public function setLimit($limit) {
		$this->_limit = $limit;
		return $this;
	}
	public function setWhere($where) {
		$this->_where = $where;
		return $this;
	}
	public function addWhere($where) {
		if(empty($this->_where) && !is_string($where)) {
			$this->setWhere( $where );
		} elseif(is_array($this->_where) && is_array($where)) {
			$this->_where = array_merge($this->_where, $where);
		} elseif(is_string($where)) {
			if(!isset($this->_where['additionalCondition']))
				$this->_where['additionalCondition'] = '';
			if(!empty($this->_where['additionalCondition']))
				$this->_where['additionalCondition'] .= ' AND ';
			$this->_where['additionalCondition'] .= $where;
			//$this->_stringWhere .= $where;	// Unused for now
		}
		return $this;
	}
	public function setSelectFields($selectFields) {
		if(is_array($selectFields))
			$selectFields = implode(',', $selectFields);
		$this->_selectFields = $selectFields;
		return $this;
	}
	public function groupBy($groupBy) {
		$this->_groupBy = $groupBy;
		return $this;
	}
	public function getLastGetCount() {
		return $this->_lastGetCount;
	}
	public function getFromTbl($params = array()) {
		$this->_lastGetCount = 0;
		$tbl = isset($params['tbl']) ? $params['tbl'] : $this->_tbl;
		$table = frameLcs::_()->getTable( $tbl );
		$this->_buildQuery( $table );
		$return = isset($params['return']) ? $params['return'] : 'all';
		$data = $table->get($this->_selectFields, $this->_where, '', $return);
		if(!empty($data)) {
			switch($return) {
				case 'one':
					$this->_lastGetCount = 1;
					break;
				case 'row':
					$data = $this->_afterGetFromTbl( $data );
					$this->_lastGetCount = 1;
					break;
				default:
					foreach($data as $i => $row) {
						$data[ $i ] = $this->_afterGetFromTbl( $row );
					}
					$this->_lastGetCount = count( $data );
					break;
			}
		}
		$this->_clearQuery( $params );
		return $data;
	}
	protected function _clearQuery($params = array()) {
		$clear = isset($params['clear']) ? $params['clear'] : array();
		if(!is_array($clear))
			$clear = array($clear);
		if(empty($clear) || in_array('limit', $clear))
			$this->_limit = '';
		if(empty($clear) || in_array('orderBy', $clear))
			$this->_orderBy = '';
		if(empty($clear) || in_array('sortOrder', $clear))
			$this->_sortOrder = '';
		if(empty($clear) || in_array('where', $clear))
			$this->_where = '';
		if(empty($clear) || in_array('selectFields', $clear))
			$this->_selectFields = '*';
		if(empty($clear) || in_array('groupBy', $clear))
			$this->_groupBy = '';
	}
	public function getCount($params = array()) {
		$tbl = isset($params['tbl']) ? $params['tbl'] : $this->_tbl;
		$table = frameLcs::_()->getTable( $tbl );
		$this->setSelectFields('COUNT(*) AS total');
		$this->_buildQuery( $table );
		$data = (int) $table->get($this->_selectFields, $this->_where, '', 'one');
		$this->_clearQuery($params);
		return $data;
	}
	protected function _afterGetFromTbl( $row ) {	// You can re-define this method in your own model
		return $row;
	}
	protected function _buildQuery($table = null) {
		if(!$table)
			$table = frameLcs::_()->getTable( $this->_tbl );
		if(!empty($this->_orderBy)) {
			$order = $this->_orderBy;
			if(!empty($this->_sortOrder))
				$order .= ' '. strtoupper($this->_sortOrder);
			$table->orderBy( $order );
		}
		if(!empty($this->_groupBy)) {
			$table->groupBy( $this->_groupBy );
		}
		if(!empty($this->_limit)) {
			$table->setLimit( $this->_limit );
		}
	}
	protected function _afterRemove($ids) {
		
	}
	public function removeGroup($ids, $key = 'id') {
		if(!is_array($ids))
			$ids = array($ids);
		// Remove all empty values
		$ids = array_filter(array_map('intval', $ids));
		if(!empty($ids)) {
			if(frameLcs::_()->getTable( $this->_tbl )->delete(array('additionalCondition' => $key. ' IN ('. implode(',', $ids). ')'))) {
				$this->_afterRemove( $ids );
				return true;
			} else 
				$this->pushError (__('Database error detected', LCS_LANG_CODE));
		} else
			$this->pushError(__('Invalid ID', LCS_LANG_CODE));
		return false;
	}
	public function clear() {
		return $this->delete();	// Just delete all
	}
	public function delete($params = array()) {
		if(frameLcs::_()->getTable( $this->_tbl )->delete( $params )) {
			return true;
		} else 
			$this->pushError (__('Database error detected', LCS_LANG_CODE));
		return false;
	}
	public function getById($id) {
		return $this->getBy($this->_idField, $id, true);
	}
	public function getBy($field, $value, $one = false) {
		$data = $this->setWhere(array($field => $value))->getFromTbl();
		//var_dump($field, $value, $data, $one);
		if(!empty($data)) {
			return $one ? array_shift($data) : $data;
		}
		return false;
	}
	public function insert($data) {
		$data = $this->_dataSave($data, false);
		$id = frameLcs::_()->getTable( $this->_tbl )->insert( $data );
		if($id) {
			return $id;
		}
		$this->pushError(frameLcs::_()->getTable( $this->_tbl )->getErrors());
		return false;
	}
	public function updateById($data, $id = 0) {
		if(!$id) {
			$id = isset($data[ $this->_idField ]) ? (int) $data[ $this->_idField ] : 0;
		}
		if($id) {
			return $this->update($data, array($this->_idField => $id));
		} else
			$this->pushError(__('Empty or invalid ID', LCS_LANG_CODE));
		return false;
	}
	public function update($data, $where) {
		$data = $this->_dataSave($data, true);
		if(frameLcs::_()->getTable( $this->_tbl )->update( $data, $where )) {
			return true;
		}
		$this->pushError(frameLcs::_()->getTable( $this->_tbl )->getErrors());
		return false;
	}
	protected function _dataSave($data, $update = false) {
		return $data;
	}
	public function getTbl() {
		return $this->_tbl;
	}
	/**
	 * We can re-define this method to not retrive all data - for simple tables
	 */
	public function setSimpleGetFields() {
		return $this;
	}
	/**
	 * Access to other module models - right from any other model
	 */
	public function getModel($code = '') {
		return frameLcs::_()->getModule( $this->_code )->getController()->getModel($code);
	}
	public function exists($field, $value) {
		return frameLcs::_()->getTable( $this->_tbl )->exists($value, $field);	// Yes, their order are reverted in tableLcs::exists() ....
	}
}