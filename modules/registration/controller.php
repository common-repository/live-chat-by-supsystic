<?php
class registrationControllerLcs extends controllerLcs {
	public function register() {
		$res = new responseLcs();
		$data = reqLcs::get('post');
		$id = isset($data['id']) ? (int) $data['id'] : 0;
		$nonce = isset($_REQUEST['_wpnonce']) ? $_REQUEST['_wpnonce'] : reqLcs::getVar('_wpnonce');
		if(!wp_verify_nonce($nonce, 'register-'. $id)) {
			die('Some error with your request.........');
		}
		
		reqLcs::startSession();
		
		if($this->getModel()->register(reqLcs::get('post'))) {
			
		} else {
			$res->pushError ($this->getModel()->getErrors());
		}
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array()
			),
		);
	}
}

