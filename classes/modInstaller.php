<?php
class modInstallerLcs {
    static private $_current = array();
    /**
     * Install new moduleLcs into plugin
     * @param string $module new moduleLcs data (@see classes/tables/modules.php)
     * @param string $path path to the main plugin file from what module is installed
     * @return bool true - if install success, else - false
     */
    static public function install($module, $path) {
        $exPlugDest = explode('plugins', $path);
        if(!empty($exPlugDest[1])) {
            $module['ex_plug_dir'] = str_replace(DS, '', $exPlugDest[1]);
        }
        $path = $path. DS. $module['code'];
        if(!empty($module) && !empty($path) && is_dir($path)) {
            if(self::isModule($path)) {
                $filesMoved = false;
                if(empty($module['ex_plug_dir']))
                    $filesMoved = self::moveFiles($module['code'], $path);
                else
                    $filesMoved = true;     //Those modules doesn't need to move their files
                if($filesMoved) {
                    if(frameLcs::_()->getTable('modules')->exists($module['code'], 'code')) {
                        frameLcs::_()->getTable('modules')->delete(array('code' => $module['code']));
                    }
					if($module['code'] != 'license')
						$module['active'] = 0;
                    frameLcs::_()->getTable('modules')->insert($module);
                    self::_runModuleInstall($module);
                    self::_installTables($module);
                    return true;
                } else {
                    errorsLcs::push(sprintf(__('Move files for %s failed'), $module['code']), errorsLcs::MOD_INSTALL);
                }
            } else
                errorsLcs::push(sprintf(__('%s is not plugin module'), $module['code']), errorsLcs::MOD_INSTALL);
        }
        return false;
    }
    static protected function _runModuleInstall($module, $action = 'install') {
        $moduleLocationDir = LCS_MODULES_DIR;
        if(!empty($module['ex_plug_dir']))
            $moduleLocationDir = utilsLcs::getPluginDir( $module['ex_plug_dir'] );
        if(is_dir($moduleLocationDir. $module['code'])) {
			if(!class_exists($module['code']. strFirstUp(LCS_CODE))) {
				importClassLcs($module['code'], $moduleLocationDir. $module['code']. DS. 'mod.php');
			}
            $moduleClass = toeGetClassNameLcs($module['code']);
            $moduleObj = new $moduleClass($module);
            if($moduleObj) {
                $moduleObj->$action();
            }
        }
    }
    /**
     * Check whether is or no module in given path
     * @param string $path path to the module
     * @return bool true if it is module, else - false
     */
    static public function isModule($path) {
        return true;
    }
    /**
     * Move files to plugin modules directory
     * @param string $code code for module
     * @param string $path path from what module will be moved
     * @return bool is success - true, else - false
     */
    static public function moveFiles($code, $path) {
        if(!is_dir(LCS_MODULES_DIR. $code)) {
            if(mkdir(LCS_MODULES_DIR. $code)) {
                utilsLcs::copyDirectories($path, LCS_MODULES_DIR. $code);
                return true;
            } else
                errorsLcs::push(__('Cannot create module directory. Try to set permission to '. LCS_MODULES_DIR. ' directory 755 or 777', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
        } else
            return true;
        return false;
    }
    static private function _getPluginLocations() {
        $locations = array();
        $plug = reqLcs::getVar('plugin');
        if(empty($plug)) {
            $plug = reqLcs::getVar('checked');
            $plug = $plug[0];
        }
        $locations['plugPath'] = plugin_basename( trim( $plug ) );
        $locations['plugDir'] = dirname(WP_PLUGIN_DIR. DS. $locations['plugPath']);
		$locations['plugMainFile'] = WP_PLUGIN_DIR. DS. $locations['plugPath'];
        $locations['xmlPath'] = $locations['plugDir']. DS. 'install.xml';
		$locations['extendModPath'] = $locations['plugDir']. DS. 'install.php';
        return $locations;
    }
    static private function _getModulesFromXml($xmlPath) {
        if($xml = utilsLcs::getXml($xmlPath)) {
            if(isset($xml->modules) && isset($xml->modules->mod)) {
                $modules = array();
                $xmlMods = $xml->modules->children();
                foreach($xmlMods->mod as $mod) {
                    $modules[] = $mod;
                }
                if(empty($modules))
                    errorsLcs::push(__('No modules were found in XML file', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
                else
                    return $modules;
            } else
                errorsLcs::push(__('Invalid XML file', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
        } else
            errorsLcs::push(__('No XML file were found', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
        return false;
    }
	static private function _getExtendModules($locations) {
		$modules = array();
		$isExtendModPath = file_exists($locations['extendModPath']);
		$modulesList = $isExtendModPath ? include $locations['extendModPath'] : self::_getModulesFromXml($locations['xmlPath']);
		if(!empty($modulesList)) {
			foreach($modulesList as $mod) {
				$modData = $isExtendModPath ? $mod : utilsLcs::xmlNodeAttrsToArr($mod);
				array_push($modules, $modData);
			}
			if(empty($modules))
				errorsLcs::push(__('No modules were found in installation file', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
			else
				return $modules;
		} else
			errorsLcs::push(__('No installation file were found', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
		return false;
	}
    /**
     * Check whether modules is installed or not, if not and must be activated - install it
     * @param array $codes array with modules data to store in database
     * @param string $path path to plugin file where modules is stored (__FILE__ for example)
     * @return bool true if check ok, else - false
     */
    static public function check($extPlugName = '') {
		if(LCS_TEST_MODE) {
			add_action('activated_plugin', array(frameLcs::_(), 'savePluginActivationErrors'));
		}
        $locations = self::_getPluginLocations();
		if($modules = self::_getExtendModules($locations)) {
			foreach($modules as $m) {
				if(!empty($m)) {
					//If module Exists - just activate it, we can't check this using frameLcs::moduleExists because this will not work for multy-site WP
					if(frameLcs::_()->getTable('modules')->exists($m['code'], 'code') /*frameLcs::_()->moduleExists($m['code'])*/) {
						self::activate($m);
					} else {                                           //  if not - install it
						if(!self::install($m, $locations['plugDir'])) {
							errorsLcs::push(sprintf(__('Install %s failed'), $m['code']), errorsLcs::MOD_INSTALL);
						}
					}
				}
			}
		} else
            errorsLcs::push(__('Error Activate module', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
        if(errorsLcs::haveErrors(errorsLcs::MOD_INSTALL)) {
            self::displayErrors();
            return false;
        }
		update_option(LCS_CODE. '_full_installed', 1);
        return true;
    }
    /**
	 * Public alias for _getCheckRegPlugs()
	 */
	/**
	 * We will run this each time plugin start to check modules activation messages
	 */
	static public function checkActivationMessages() {

	}
    /**
     * Deactivate module after deactivating external plugin
     */
    static public function deactivate() {
        $locations = self::_getPluginLocations();
		if($modules = self::_getExtendModules($locations)) {
			foreach($modules as $m) {
				if(frameLcs::_()->moduleActive($m['code'])) { //If module is active - then deacivate it
					if(frameLcs::_()->getModule('options')->getModel('modules')->put(array(
						'id' => frameLcs::_()->getModule($m['code'])->getID(),
						'active' => 0,
					))->error) {
						errorsLcs::push(__('Error Deactivation module', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
					}
				}
			}
		}
        if(errorsLcs::haveErrors(errorsLcs::MOD_INSTALL)) {
            self::displayErrors(false);
            return false;
        }
        return true;
    }
    static public function activate($modDataArr) {
		if(!empty($modDataArr['code']) && !frameLcs::_()->moduleActive($modDataArr['code'])) { //If module is not active - then acivate it
			if(frameLcs::_()->getModule('options')->getModel('modules')->put(array(
				'code' => $modDataArr['code'],
				'active' => 1,
			))->error) {
				errorsLcs::push(__('Error Activating module', LCS_LANG_CODE), errorsLcs::MOD_INSTALL);
			} else {
				$dbModData = frameLcs::_()->getModule('options')->getModel('modules')->get(array('code' => $modDataArr['code']));
				if(!empty($dbModData) && !empty($dbModData[0])) {
					$modDataArr['ex_plug_dir'] = $dbModData[0]['ex_plug_dir'];
				}
				self::_runModuleInstall($modDataArr, 'activate');
			}
		}
    }
    /**
     * Display all errors for module installer, must be used ONLY if You realy need it
     */
    static public function displayErrors($exit = true) {
        $errors = errorsLcs::get(errorsLcs::MOD_INSTALL);
        foreach($errors as $e) {
            echo '<b style="color: red;">'. $e. '</b><br />';
        }
        if($exit) exit();
    }
    static public function uninstall() {
        $locations = self::_getPluginLocations();
		if($modules = self::_getExtendModules($locations)) {
			foreach($modules as $m) {
				self::_uninstallTables($m);
				frameLcs::_()->getModule('options')->getModel('modules')->delete(array('code' => $m['code']));
				utilsLcs::deleteDir(LCS_MODULES_DIR. $m['code']);
			}
		}
    }
    static protected  function _uninstallTables($module) {
        if(is_dir(LCS_MODULES_DIR. $module['code']. DS. 'tables')) {
            $tableFiles = utilsLcs::getFilesList(LCS_MODULES_DIR. $module['code']. DS. 'tables');
            if(!empty($tableNames)) {
                foreach($tableFiles as $file) {
                    $tableName = str_replace('.php', '', $file);
                    if(frameLcs::_()->getTable($tableName))
                        frameLcs::_()->getTable($tableName)->uninstall();
                }
            }
        }
    }
    static public function _installTables($module, $action = 'install') {
		$modDir = empty($module['ex_plug_dir']) ?
            LCS_MODULES_DIR. $module['code']. DS :
            utilsLcs::getPluginDir($module['ex_plug_dir']). $module['code']. DS;
        if(is_dir($modDir. 'tables')) {
            $tableFiles = utilsLcs::getFilesList($modDir. 'tables');
            if(!empty($tableFiles)) {
                frameLcs::_()->extractTables($modDir. 'tables'. DS);
                foreach($tableFiles as $file) {
                    $tableName = str_replace('.php', '', $file);
                    if(frameLcs::_()->getTable($tableName))
                        frameLcs::_()->getTable($tableName)->$action();
                }
            }
        }
    }
}
