<?php
class statisticsViewLcs extends viewLcs {
	public function displayAdminPromoTabContent() {
		return parent::getContent('statPromoGraph');
	}
}
