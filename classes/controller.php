<?php
abstract class controllerLcs {
	protected $_models = array();
	protected $_views = array();
	protected $_task = '';
	protected $_defaultView = '';
	protected $_code = '';
	/*
	 * Model name can be specified for any getListForTbl requests
	 */
	protected $_listModelName = '';
	public function __construct($code) {
		$this->setCode($code);
		$this->_defaultView = $this->getCode();
	}
	public function init() {
		/*load model and other preload data goes here*/
	}
	protected function _onBeforeInit() {

	}
	protected function _onAfterInit() {

	}
	public function setCode($code) {
		$this->_code = $code;
	}
	public function getCode() {
		return $this->_code;
	}
	public function exec($task = '') {
		if(method_exists($this, $task)) {
			$this->_task = $task;   //For multicontrollers module version - who know, maybe that's will be?))
			return $this->$task();
		}
		return null;
	}
	public function getView($name = '') {
		if(empty($name)) $name = $this->getCode();
		if(!isset($this->_views[$name])) {
			$this->_views[$name] = $this->_createView($name);
		}
		return $this->_views[$name];
	}
	public function getModel($name = '') {
		if(!$name)
			$name = $this->_code;
		if(!isset($this->_models[$name])) {
			$this->_models[$name] = $this->_createModel($name);
		}
		return $this->_models[$name];
	}
	protected function _createModel($name = '') {
		if(empty($name)) $name = $this->getCode();
		$parentModule = frameLcs::_()->getModule( $this->getCode() );
		$className = '';
		if(importLcs($parentModule->getModDir(). 'models'. DS. $name. '.php')) {
			$className = toeGetClassNameLcs($name. 'Model');
		}
		
		if($className) {
			$model = new $className();
			$model->setCode( $this->getCode() );
			return $model;
		}
		return NULL;
	}
	protected function _createView($name = '') {
		if(empty($name)) $name = $this->getCode();
		$parentModule = frameLcs::_()->getModule( $this->getCode() );
		$className = '';
		
		if(importLcs($parentModule->getModDir(). 'views'. DS. $name. '.php')) {
			$className = toeGetClassNameLcs($name. 'View');
		}
		
		if($className) {
			$view = new $className();
			$view->setCode( $this->getCode() );
			return $view;
		}
		return NULL;
	}
	public function display($viewName = '') {
		$view = NULL;
		if(($view = $this->getView($viewName)) === NULL) {
			$view = $this->getView();   //Get default view
		}
		if($view) {
			$view->display();
		}
	}
	public function __call($name, $arguments) {
		$model = $this->getModel();
		if(method_exists($model, $name))
			return $model->$name($arguments[0]);
		else
			return false;
	}
	/**
	 * Retrive permissions for controller methods if exist.
	 * If need - should be redefined in each controller where it required.
	 * @return array with permissions
	 * @example :
	 return array(
			S_METHODS => array(
				'save' => array(LCS_ADMIN),
				'remove' => array(LCS_ADMIN),
				'restore' => LCS_ADMIN,
			),
			S_USERLEVELS => array(
				S_ADMIN => array('save', 'remove', 'restore')
			),
		);
	 * Can be used on of sub-array - LCS_METHODS or LCS_USERLEVELS
	 */
	public function getPermissions() {
		return array();
	}
	public function getModule() {
		return frameLcs::_()->getModule( $this->getCode() );
	}
	protected function _prepareTextLikeSearch($val) {
		return '';	 // Should be re-defined for each type
	}
	protected function _prepareModelBeforeListSelect($model) {
		return $model;
	}
	/**
	 * Common method for list table data
	 */
	public function getListForTbl() {
		$res = new responseLcs();
		$res->ignoreShellData();
		
		$page = (int) reqLcs::getVar('page');
		$rowsLimit = (int) reqLcs::getVar('rows');
		$orderBy = reqLcs::getVar('sidx');
		$sortOrder = reqLcs::getVar('sord');
		$this->_listModelName = reqLcs::getVar('_model');

		$model = $this->getModel(($this->_listModelName ? $this->_listModelName : ''));
		
		// Our custom search
		$search = reqLcs::getVar('search');
		if($search && !empty($search) && is_array($search)) {
			foreach($search as $k => $v) {
				$v = trim($v);
				if(empty($v)) continue;
				if($k == 'text_like') {
					$v = $this->_prepareTextLikeSearch( $v );
					if(!empty($v)) {
						$model->addWhere(array('additionalCondition' => $v));
					}
				} else {
					$model->addWhere(array($k => $v));
				}
			}
		}
		// jqGrid search
		$isSearch = reqLcs::getVar('_search');
		if($isSearch) {
			$searchField = trim(reqLcs::getVar('searchField'));
			$searchString = trim(reqLcs::getVar('searchString'));
			if(!empty($searchField) && !empty($searchString)) {
				// For some cases - we will need to modify search keys and/or values before put it to the model
				$model->addWhere(array(
					$this->_prepareSearchField($searchField) => $this->_prepareSearchString($searchString)
				));
			}
		}
		$model = $this->_prepareModelBeforeListSelect($model);
		// Get total pages count for current request
		$totalCount = $model->getCount(array('clear' => array('selectFields')));
		$totalPages = 0;
		if($totalCount > 0) {
			$totalPages = ceil($totalCount / $rowsLimit);
		}
		if($page > $totalPages) {
			$page = $totalPages;
		}
		// Calc limits - to get data only for current set
		$limitStart = $rowsLimit * $page - $rowsLimit; // do not put $limit*($page - 1)
		if($limitStart < 0)
			$limitStart = 0;
		
 		$data = $model
			->setLimit($limitStart. ', '. $rowsLimit)
			->setOrderBy( $this->_prepareSortOrder($orderBy) )
			->setSortOrder( $sortOrder )
			->setSimpleGetFields()
			->getFromTbl();
		
		$data = $this->_prepareListForTbl( $data );
		$res->addData('page', $page);
		$res->addData('total', $totalPages);
		$res->addData('rows', $data);
		$res->addData('records', $model->getLastGetCount());
		$res = dispatcherLcs::applyFilters($this->getCode(). '_getListForTblResults', $res);
		$this->_listModelName = '';
		$res->ajaxExec();
	}
	public function removeGroup() {
		$res = new responseLcs();
		$this->_listModelName = reqLcs::getVar('_model');
		$model = $this->getModel(($this->_listModelName ? $this->_listModelName : ''));
		
		if($model->removeGroup(reqLcs::getVar('listIds', 'post'))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($model->getErrors());
		$this->_listModelName = '';
		$res->ajaxExec();
	}
	public function clear() {
		$res = new responseLcs();
		if($this->getModel()->clear()) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	protected function _prepareListForTbl($data) {
		return $data;
	}
	protected function _prepareSearchField($searchField) {
		return $searchField;
	}
	protected function _prepareSearchString($searchString) {
		return $searchString;
	}
	protected function _prepareSortOrder($sortOrder) {
		return $sortOrder;
	}
}
