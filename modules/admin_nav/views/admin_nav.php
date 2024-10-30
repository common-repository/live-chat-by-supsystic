<?php
class admin_navViewLcs extends viewLcs {
	public function getBreadcrumbs() {
		$this->assign('breadcrumbsList', dispatcherLcs::applyFilters('mainBreadcrumbs', $this->getModule()->getBreadcrumbsList()));
		return parent::getContent('adminNavBreadcrumbs');
	}
}
