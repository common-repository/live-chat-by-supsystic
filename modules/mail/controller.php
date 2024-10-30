<?php
class mailControllerLcs extends controllerLcs {
	public function testEmail() {
		$res = new responseLcs();
		$email = reqLcs::getVar('test_email', 'post');
		if($this->getModel()->testEmail($email)) {
			$res->addMessage(__('Now check your email inbox / spam folders for test mail.'));
		} else 
			$res->pushError ($this->getModel()->getErrors());
		$res->ajaxExec();
	}
	public function saveMailTestRes() {
		$res = new responseLcs();
		$result = (int) reqLcs::getVar('result', 'post');
		frameLcs::_()->getModule('options')->getModel()->save('mail_function_work', $result);
		$res->ajaxExec();
	}
	public function saveOptions() {
		$res = new responseLcs();
		$optsModel = frameLcs::_()->getModule('options')->getModel();
		$submitData = reqLcs::get('post');
		if($optsModel->saveGroup($submitData)) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError ($optsModel->getErrors());
		$res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array('testEmail', 'saveMailTestRes', 'saveOptions')
			),
		);
	}
}
