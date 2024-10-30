<?php
class chatModelLcs extends modelLcs {
	private $_engineTxtEditors = array(
		'wait_txt', 'before_reg_txt', 'chat_header_txt', 'complete_txt'
	);
	public function saveSettings($d = array()) {
		/*if(isset($d['opts']) && !empty($d['opts'])) {
			if(!frameLcs::_()->getModule('options')->getModel()->saveGroup(array('opt_values' => $d['opts']))) {
				$this->pushError( frameLcs::_()->getModule('options')->getModel()->getErrors() );
				return false;
			}
		}*/
		if(isset($d['tpl']) && !empty($d['tpl'])) {
			if(empty($d['tpl']['engine_id'])) {
				$d['tpl']['engine_id'] = $this->getDefaultEngineId();
			}
			if(!$this->getModel('chat_templates')->save( $d['tpl'] )) {
				$this->pushError( $this->getModel()->getErrors('chat_templates') );
				return false;
			}
		}
		if(isset($d['engine']) && !empty($d['engine'])) {
			if(!$this->getModel('chat_engines')->save( $d['engine'] )) {
				$this->pushError( $this->getModel()->getErrors('chat_engines') );
				return false;
			}
		}
		return false;
	}
	public function getCurrentTpl() {
		$engine = $this->getModel()->getCurrentEngine();
		return $this->getModel('chat_templates')->getBy('engine_id', $engine['id'], true);
	}
	public function getDefaultEngineId() {
		$defId = (int) frameLcs::_()->getModule('options')->get('default_chat_engine');
		if(!$defId) {
			$defId = LCS_DEF_ENGINE_ID;
		}
		return $defId;
	}
	public function setDefaultEngineId($id) {
		frameLcs::_()->getModule('options')->getModel()->save('default_chat_engine', $id);
	}
	public function getCurrentEngine() {
		return $this->getModel('chat_engines')->getById( $this->getDefaultEngineId() );
	}
	public function getEngineTxtEditors() {
		return $this->_engineTxtEditors;
	}
	public function sendChatToEmail($d = array()) {
		$d = dbLcs::prepareHtmlIn( $d );
		$email = isset($d['email']) ? trim($d['email']) : false;
		$content = isset($d['chat_content']) ? trim($d['chat_content']) : false;
		if(!empty($email)) {
			if(!empty($content)) {
				$siteName = wp_specialchars_decode(get_bloginfo('name'));
				$adminEmail = get_bloginfo('admin_email');
				if(frameLcs::_()->getModule('mail')->send(
					$email,
					sprintf(_('Chat from %s', LCS_LANG_CODE), $siteName),
					$content,
					$siteName,
					$adminEmail
				)) {
					return true;
				} else
					$this->pushError (frameLcs::_()->getModule('mail')->getMailErrors());
			} else 
				$this->pushError (__('Empty content', LCS_LANG_CODE));
		} else
			$this->pushError (__('Empty email', LCS_LANG_CODE));
		return false;
	}
}
