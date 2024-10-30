<?php
class registrationLcs extends moduleLcs {
	public function generateUserFields($chatEngine) {
		$resHtml = '';
		$registrationNotMandatory = $chatEngine['params']['reg_not_mandatory'];
		foreach($chatEngine['params']['reg_fields'] as $k => $f) {
			if(isset($f['enb']) && $f['enb']) {
				$htmlType = $f['html'];
				$name = $f['name'];
				if($name == 'email') {
					$htmlType = 'email';
				}
				$htmlParams = array(
					'placeholder' => $f['label'],
				);
				if($htmlType == 'selectbox' && isset($f['options']) && !empty($f['options'])) {
					$htmlParams['options'] = array();
					foreach($f['options'] as $opt) {
						$htmlParams['options'][ $opt['name'] ] = $opt['label'];
					}
				}
				if(isset($f['value']) && !empty($f['value'])) {
					$htmlParams['value'] = $f['value'];
				}
				if(isset($f['mandatory']) && !empty($f['mandatory']) && (int)$f['mandatory'] && !$registrationNotMandatory) {
					$htmlParams['required'] = true;
				}
				$inputName = in_array($name, array('name', 'email')) ? $name : 'fields['. $name. ']';
				if(empty($htmlType) || !method_exists('htmlLcs', $htmlType)) continue;	// If for some reason it is empty - just skip it
				$inputHtml = htmlLcs::$htmlType($inputName, $htmlParams);
				if($htmlType == 'selectbox') {
					$inputHtml = '<label class="lcsRegSelect"><span class="lcsRegSelectLabel">'. $f['label']. ': </span>'. $inputHtml. '</label>';
				}
				$resHtml .= $inputHtml;
			}
		}
		return $resHtml;
	}
	public function generateFormStart($chatEngine) {
		$res = '<form class="lcsRegForm" action="'. LCS_SITE_URL. '" method="post">';
		return $res;
	}
	public function generateFormEnd($chatEngine) {
		$res = '';
		$res .= htmlLcs::hidden('mod', array('value' => 'registration'));
		$res .= htmlLcs::hidden('action', array('value' => 'register'));
		$res .= htmlLcs::hidden('id', array('value' => $chatEngine['id']));
		$res .= htmlLcs::hidden('url', array('value' => uriLcs::getFullUrl()));
		$res .= htmlLcs::hidden('_wpnonce', array('value' => wp_create_nonce('register-'. $chatEngine['id'])));
		$res .= '<div class="lcsRegMsg"></div>';
		$res .= '</form>';
		return $res;
	}
}

