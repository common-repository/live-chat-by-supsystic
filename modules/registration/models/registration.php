<?php
class registrationModelLcs extends modelLcs {
	public function register($d = array()) {
		$id = isset($d['id']) ? (int) $d['id'] : 0;
		$url = isset($d['url']) ? htmlspecialchars(trim($d['url'])) : '';
		if(!empty($id) && !empty($url)) {
			$d = dbLcs::prepareHtmlIn($d);
			$engine = frameLcs::_()->getModule('chat')->getModel('chat_engines')->getById($id);
			if($engine) {
				$registrationNotMandatory = $engine['params']['reg_not_mandatory'];
				$vailidated = $this->validateInput($d, $engine);
				if($vailidated || $registrationNotMandatory) {
					$d['position'] = frameLcs::_()->getModule('chat')->getModel('chat_users')->getPositionId('user');
					$uid = 0;
					if(($vailidated && ($uid = frameLcs::_()->getModule('chat')->getModel('chat_users')->save($d, $engine))) || $registrationNotMandatory) {
						if($uid) {
							frameLcs::_()->getModule('chat')->setCurrentUserId( $uid );
						}
						if(frameLcs::_()->getModule('chat')->getModel('chat_sessions')->startSession(array(
							'engine_id' => $engine['id'],
							'user_id' => $uid,
							'url' => $d['url'],
						))) {
							return true;
						} else
							$this->pushError(frameLcs::_()->getModule('chat')->getModel('chat_sessions')->getErrors());
					} else 
						$this->pushError(frameLcs::_()->getModule('chat')->getModel('chat_users')->getErrors());
				}
			} else
				$this->pushError (__('Can not find Engine', LCS_LANG_CODE));
		} else 
			$this->pushError (__('Empty Engine ID or URL', LCS_LANG_CODE));
		/*if(isset($registrationNotMandatory) && $registrationNotMandatory) {
			return true;
		}*/
		return false;
	}
	public function validateInput($d, $engine) {
		if(isset($engine['params']['reg_fields']) && !empty($engine['params']['reg_fields'])) {
			$errors = array();
			foreach($engine['params']['reg_fields'] as $i => $f) {
				if(isset($f['enb']) && $f['enb'] && isset($f['mandatory']) && $f['mandatory']) {
					$name = $f['name'];
					if(in_array($name, array('name', 'email'))) {
						$value = isset($d[ $name ]) ? trim($d[ $name ]) : false;
					} else {
						$value = isset($d['fields'][ $name ]) ? trim($d['fields'][ $name ]) : false;
					}
					if(empty($value)) {
						$errors[ $name ] = sprintf($f['html'] == 'selectbox' 
							? __('Please select %s', LCS_LANG_CODE)
							: __('Please enter %s', LCS_LANG_CODE)
						, $f['label']);
					}
				}
			}
			if(!empty($errors)) {
				$this->pushError($errors);
				return false;
			}
		}
		return true;
	}
}
