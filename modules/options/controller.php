<?php
class optionsControllerLcs extends controllerLcs {
	public function saveGroup() {
		$res = new responseLcs();
		if($this->getModel()->saveGroup(reqLcs::get('post'))) {
			$res->addMessage(__('Done', LCS_LANG_CODE));
		} else
			$res->pushError ($this->getModel('options')->getErrors());
		return $res->ajaxExec();
	}
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array('saveGroup')
			),
		);
	}
}

