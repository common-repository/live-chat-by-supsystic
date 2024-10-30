<?php
class mailViewLcs extends viewLcs {
	public function getTabContent() {
		frameLcs::_()->getModule('templates')->loadJqueryUi();
		frameLcs::_()->addScript('admin.'. $this->getCode(), $this->getModule()->getModPath(). 'js/admin.'. $this->getCode(). '.js');
		
		$this->assign('options', frameLcs::_()->getModule('options')->getCatOpts( $this->getCode() ));
		$this->assign('testEmail', frameLcs::_()->getModule('options')->get('notify_email'));
		return parent::getContent('mailAdmin');
	}
}
