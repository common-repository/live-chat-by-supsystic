<?php
class chat_templatesModelLcs extends modelLcs {
	private $_linksReplacement = array();
	
	public function __construct() {
		$this->_setTbl('chat_templates');
	}
	/**
	 * Exclude some data from list - to avoid memory overload
	 */
	public function getSimpleTplsList($where = array(), $params = array()) {
		if($where)
			$this->setWhere ($where);
		return $this->setSimpleGetFields()->getFromTbl( $params );
	}
	protected function _prepareParamsAfterDb($params) {
		if(is_array($params)) {
			foreach($params as $k => $v) {
				$params[ $k ] = $this->_prepareParamsAfterDb( $v ); 
			}
		} else
			$params = stripslashes ($params);
		return $params;
	}
	private function _getLinksReplacement() {
		if(empty($this->_linksReplacement)) {
			$this->_linksReplacement = array(
				'modUrl' => array('url' => $this->getModule()->getModPath(), 'key' => 'LCS_MOD_URL'),
				'siteUrl' => array('url' => LCS_SITE_URL, 'key' => 'LCS_SITE_URL'),
				'assetsUrl' => array('url' => $this->getModule()->getAssetsUrl(), 'key' => 'LCS_ASSETS_URL'),
				'oldAssets' => array('url' => $this->getModule()->getOldAssetsUrl(), 'key' => 'LCS_OLD_ASSETS_URL'),
			);
		}
		return $this->_linksReplacement;
	}
	protected function _beforeDbReplace($data) {
		static $replaceFrom, $replaceTo;
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$data[ $k ] = $this->_beforeDbReplace($v);
			}
		} else {
			if(!$replaceFrom) {
				$this->_getLinksReplacement();
				foreach($this->_linksReplacement as $k => $rData) {
					if($k == 'oldAssets') {	// Replace old assets urls - to new one
						$replaceFrom[] = $rData['url'];
						$replaceTo[] = '['. $this->_linksReplacement['assetsUrl']['key']. ']';
					} else {
						$replaceFrom[] = $rData['url'];
						$replaceTo[] = '['. $rData['key']. ']';
					}
				}
			}
			$data = str_replace($replaceFrom, $replaceTo, $data);
		}
		return $data;
	}
	protected function _afterDbReplace($data) {
		static $replaceFrom, $replaceTo;
		if(is_array($data)) {
			foreach($data as $k => $v) {
				$data[ $k ] = $this->_afterDbReplace($v);
			}
		} else {
			if(!$replaceFrom) {
				$this->_getLinksReplacement();
				/*Tmp fix - for quick replace all mode URL to assets URL*/
				$replaceFrom[] = '['. $this->_linksReplacement['modUrl']['key']. ']';
				$replaceTo[] = '['. $this->_linksReplacement['assetsUrl']['key']. ']';
				$replaceFrom[] = $this->_linksReplacement['oldAssets']['url'];
				$replaceTo[] = $this->_linksReplacement['assetsUrl']['url'];
				/*****/
				foreach($this->_linksReplacement as $k => $rData) {
					$replaceFrom[] = '['. $rData['key']. ']';
					$replaceTo[] = $rData['url'];
				}
			}
			$data = str_replace($replaceFrom, $replaceTo, $data);
		}
		return $data;
	}
	protected function _afterGetFromTbl($row) {
		if(isset($row['params']))
			$row['params'] = $this->_prepareParamsAfterDb( utilsLcs::unserialize( base64_decode($row['params']) ) );
		if(empty($row['img_preview'])) {
			$row['img_preview'] = str_replace(' ', '-', strtolower( trim($row['label']) )). '.jpg';
		}
		$row['img_preview_url'] = uriLcs::_($this->getModule()->getAssetsUrl(). 'img/preview/'. $row['img_preview']);
		$row['view_id'] = $row['id']. '_'. mt_rand(1, 999999);
		$row['view_html_id'] = 'lcsShell_'. $row['view_id'];
		$row = $this->_afterDbReplace($row);
		return $row;
	}
	protected function _dataSave($data, $update = false) {
		$data = $this->_beforeDbReplace($data);
		if(isset($data['params']))
			$data['params'] = base64_encode(utilsLcs::serialize( $data['params'] ));
		return $data;
	}
	protected function _escTplData($data) {
		$data['label'] = dbLcs::prepareHtmlIn($data['label']);
		$data['html'] = dbLcs::escape($data['html']);
		$data['css'] = dbLcs::escape($data['css']);
		return $data;
	}
	public function insertFromOriginal($original) {
		$original = $this->_escTplData( $original );
		return $this->insert( $original );
	}
	public function remove($id) {
		$id = (int) $id;
		if($id) {
			if(frameLcs::_()->getTable( $this->_tbl )->delete(array('id' => $id))) {
				return true;
			} else
				$this->pushError (__('Database error detected', LCS_LANG_CODE));
		} else
			$this->pushError(__('Invalid ID', LCS_LANG_CODE));
		return false;
	}
	/**
	 * Do not remove pre-set templates
	 */
	public function clear() {
		if(frameLcs::_()->getTable( $this->_tbl )->delete(array('additionalCondition' => 'original_id != 0'))) {
			return true;
		} else 
			$this->pushError (__('Database error detected', LCS_LANG_CODE));
		return false;
	}
	public function save($d = array()) {
		if(isset($d['params']['opts_attrs']['txt_block_number']) && !empty($d['params']['opts_attrs']['txt_block_number'])) {
			for($i = 0; $i < (int) $d['params']['opts_attrs']['txt_block_number']; $i++) {
				$sendValKey = 'params_tpl_txt_val_'. $i;
				if(isset($d[ $sendValKey ])) {
					$d['params']['tpl']['txt_'. $i] = urldecode( $d[ $sendValKey ] );
				}
			}
		}
		$res = $this->updateById($d);
		if($res) {
			dispatcherLcs::doAction('afterChatTplUpdate', $d);
		}
		return $res;
	}
	public function updateParamsById($d) {
		foreach($d as $k => $v) {
			if(!in_array($k, array('id', 'params')))
				unset($d[ $k ]);
		}
		return $this->updateById($d);
	}
	public function changeTpl($d = array()) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		$d['new_tpl_id'] = isset($d['new_tpl_id']) ? (int) $d['new_tpl_id'] : 0;
		if($d['id'] && $d['new_tpl_id']) {
			$currentTpl = $this->getById( $d['id'] );
			$newTpl = $this->getById( $d['new_tpl_id'] );
			//$originalTpl = $this->getById( $currentTpl['original_id'] );
			/*$diffFromOriginal = $this->getDifferences($currentTpl, $originalTpl);
			if(!empty($diffFromOriginal)) {
				if(isset($newTpl['params'])) {
					foreach($diffFromOriginal as $k) {
						if(strpos($k, 'params.tpl.enb_sm_') === 0 
							|| strpos($k, 'params.tpl.sm_') === 0 
							|| strpos($k, 'params.tpl.enb_sub_') === 0 
							|| strpos($k, 'params.tpl.sub_') === 0
							|| strpos($k, 'params.tpl.enb_txt_') === 0
							|| strpos($k, 'params.tpl.txt_') === 0
						) {
							$this->_assignKeyArr($currentTpl, $newTpl, $k);
						}
					}
				}
			}*/
			frameLcs::_()->getModule('supsystic_promo')->getModel()->saveUsageStat('change_to_tpl.'. strtolower(str_replace(' ', '-', $newTpl['label'])));
			$newTpl['original_id'] = $newTpl['id'];	// It will be our new original
			$newTpl['id'] = $currentTpl['id'];
			$newTpl['label'] = $currentTpl['label'];
			unset($newTpl['engine_id']);
			$newTpl = dispatcherLcs::applyFilters('chatChangeTpl', $newTpl, $currentTpl);
			$newTpl = $this->_escTplData( $newTpl );
			return $this->update( $newTpl, array('id' => $newTpl['id']) );
		} else
			$this->pushError (__('Provided data was corrupted', LCS_LANG_CODE));
		return false;
	}
	private function _assignKeyArr($from, &$to, $key) {
		$subKeys = explode('.', $key);	
		// Yeah, hardcode, I know.............
		switch(count($subKeys)) {
			case 4:
				if(isset( $from[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ][ $subKeys[3] ] ))
					$to[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ][ $subKeys[3] ] = $from[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ][ $subKeys[3] ];
				else
					unset($to[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ][ $subKeys[3] ]);
				break;
			case 3:
				if(isset( $from[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ] ))
					$to[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ] = $from[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ];
				else
					unset($to[ $subKeys[0] ][ $subKeys[1] ][ $subKeys[2] ]);
				break;
			case 2:
				if(isset( $from[ $subKeys[0] ][ $subKeys[1] ] ))
					$to[ $subKeys[0] ][ $subKeys[1] ] = $from[ $subKeys[0] ][ $subKeys[1] ];
				else
					unset($to[ $subKeys[0] ][ $subKeys[1] ]);
				break;
			case 1:
				if(isset( $from[ $subKeys[0] ] ))
					$to[ $subKeys[0] ] = $from[ $subKeys[0] ];
				else
					unset( $to[ $subKeys[0] ] );
				break;
		}
	}
	public function getDifferences($tpl, $original) {
		$difsFromOriginal = $this->_computeDifferences($tpl, $original);
		$difsOfOriginal = $this->_computeDifferences($original, $tpl);	// Some options may be present in original, but not present in current chat template
		if(!empty($difsFromOriginal) && empty($difsOfOriginal)) {
			return $difsFromOriginal;
		} elseif(empty($difsFromOriginal) && !empty($difsOfOriginal)) {
			return $difsOfOriginal;
		} else {
			$difs = array_merge($difsFromOriginal, $difsOfOriginal);
			return array_unique($difs);
		}
	}
	private function _computeDifferences($tpl, $original, $key = '', $keysImplode = array()) {
		$difs = array();
		if(is_array($tpl)) {
			$excludeKey = array('id', 'label', 'active', 'original_id', 'img_preview', 'date_created', 'img_preview_url');
			if(!empty($key))
				$keysImplode[] = $key;
			foreach($tpl as $k => $v) {
				if(in_array($k, $excludeKey) && empty($key)) continue;
				if(!isset($original[ $k ])) {
					$difs[] = $this->_prepareDiffKeys($k, $keysImplode);
					continue;
				}
				$currDifs = $this->_computeDifferences($tpl[ $k ], $original[ $k ], $k, $keysImplode);
				if(!empty($currDifs)) {
					$difs = array_merge($difs, $currDifs);
				}
			}
		} else {
			if($tpl != $original) {
				$difs[] = $this->_prepareDiffKeys($key, $keysImplode);
			}
		}
		return $difs;
	}
	private function _prepareDiffKeys($key, $keysImplode) {
		return empty($keysImplode) ? $key : implode('.', $keysImplode). '.'. $key;
	}
	public function saveAsCopy($d = array()) {
		$d['copy_label'] = isset($d['copy_label']) ? trim($d['copy_label']) : '';
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		if(!empty($d['copy_label'])) {
			if(!empty($d['id'])) {
				$original = $this->getById($d['id']);
				unset($original['id']);
				unset($original['date_created']);
				$original['label'] = $d['copy_label'];
				$original['views'] = $original['unique_views'] = $original['actions'] = 0;
				//frameLcs::_()->getModule('supsystic_promo')->getModel()->saveUsageStat('save_as_copy');
				return $this->insertFromOriginal( $original );
			} else
				$this->pushError (__('Invalid ID', LCS_LANG_CODE));
		} else
			$this->pushError (__('Please enter Name', LCS_LANG_CODE), 'copy_label');
		return false;
	}
	public function setSimpleGetFields() {
		$this->setSelectFields('id, label, original_id, img_preview, date_created');
		return parent::setSimpleGetFields();
	}
	public function getForEngine($engineId) {
		return $this->getBy('engine_id', $engineId, true);
	}
	public function updateCode( $d = array() ) {
		$d['id'] = isset($d['id']) ? (int) $d['id'] : 0;
		$d['code'] = isset($d['code']) ? $d['code'] : false;
		if(!empty($d['id']) && !empty($d['code'])) {
			$tpl = $this->getById( $d['id'] );
			$originalTpl = $this->setWhere(array(
				'unique_id' => $tpl['unique_id'],
				'original_id' => 0,
			))->getFromTbl(array('return' => 'row'));
			if(!empty($tpl) && !empty($originalTpl)) {
				$unEscapedContent = $originalTpl[ $d['code'] ];
				$originalTpl = $this->_escTplData( $originalTpl );
				$newContent = $originalTpl[ $d['code'] ];
				if($this->updateById(array($d['code'] => $newContent), $d['id'])) {
					return $unEscapedContent;
				}
			} else
				$this->pushError (__('Con not find required templates', LCS_LANG_CODE));
		} else
			$this->pushError (__('Provided data was corrupted', LCS_LANG_CODE));
		return false;
	}
}
