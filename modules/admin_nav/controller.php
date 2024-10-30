<?php
class admin_navControllerLcs extends controllerLcs {
	public function getPermissions() {
		return array(
			LCS_USERLEVELS => array(
				LCS_ADMIN => array()
			),
		);
	}
}