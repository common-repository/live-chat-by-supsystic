<?php
class statisticsControllerLcs extends controllerLcs {
	public function add() {
		$res = new responseLcs();
		$connectHash = reqLcs::getVar('connect_hash', 'post');
		$id = reqLcs::getVar('id', 'post');
		if(!frameLcs::_()->getModule('chat')->checkConnectHash($connectHash, $id)) {
			$res->pushError('Some undefined for now.....');
			$res->ajaxExec( true );
		}
		if($this->getModel()->add( reqLcs::get('post') )) {
			// Do nothing for now
		} else
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function clearForEngine() {
		$res = new responseLcs();
		if($this->getModel()->clearForEngine(array('id' => LCS_DEF_ENGINE_ID))) {	// LCS_DEF_ENGINE_ID hardcoded here for now
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function getUpdatedStats() {
		$res = new responseLcs();
		if(($stats = $this->getModel()->getUpdatedStats( reqLcs::get('post') )) !== false) {
			$res->addData('stats', $stats);
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array('clearForEngine', 'getUpdatedStats')
			),
		);
	}
}
