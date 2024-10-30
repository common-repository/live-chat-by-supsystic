<?php
class pagesViewLcs extends viewLcs {
    public function displayDeactivatePage() {
        $this->assign('GET', reqLcs::get('get'));
        $this->assign('POST', reqLcs::get('post'));
        $this->assign('REQUEST_METHOD', strtoupper(reqLcs::getVar('REQUEST_METHOD', 'server')));
        $this->assign('REQUEST_URI', basename(reqLcs::getVar('REQUEST_URI', 'server')));
        parent::display('deactivatePage');
    }
}

