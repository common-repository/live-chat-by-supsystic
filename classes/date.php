<?php
class dateLcs {
	static public function _($time = NULL) {
		if(is_null($time)) {
			$time = time();
		}
		return date(LCS_DATE_FORMAT_HIS, $time);
	}
}