<?php
/**
 * Live Chat by Supsystic
 *
 * @author      supsystic.com
 * @copyright   2018 supsystic.com
 * @license     GPL-2.0+
 *
 * Plugin Name: Live Chat by Supsystic
 * Plugin URI: https://supsystic.com/
 * Description: Live Сhat help visitors and customers on your site for sales and support in real-time. Support users and capture leads with online live chat support
 * Version: 1.5.3
 * Author: supsystic.com
 * Author URI: https://supsystic.com
 * Text Domain: live-chat-by-supsystic
 * Domain Path: /languages
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */
	/**
	 * Base config constants and functions
	 */
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'config.php');
    require_once(dirname(__FILE__). DIRECTORY_SEPARATOR. 'functions.php');
	/**
	 * Connect all required core classes
	 */
    importClassLcs('dbLcs');
    importClassLcs('installerLcs');
    importClassLcs('baseObjectLcs');
    importClassLcs('moduleLcs');
    importClassLcs('modelLcs');
    importClassLcs('viewLcs');
    importClassLcs('controllerLcs');
    importClassLcs('helperLcs');
    importClassLcs('dispatcherLcs');
    importClassLcs('fieldLcs');
    importClassLcs('tableLcs');
    importClassLcs('frameLcs');
	/**
	 * @deprecated since version 1.0.1
	 */
    importClassLcs('langLcs');
    importClassLcs('reqLcs');
    importClassLcs('uriLcs');
    importClassLcs('htmlLcs');
    importClassLcs('responseLcs');
    importClassLcs('fieldAdapterLcs');
    importClassLcs('validatorLcs');
    importClassLcs('errorsLcs');
    importClassLcs('utilsLcs');
    importClassLcs('modInstallerLcs');
	importClassLcs('installerDbUpdaterLcs');
	importClassLcs('dateLcs');
	/**
	 * Check plugin version - maybe we need to update database, and check global errors in request
	 */
    installerLcs::update();
    errorsLcs::init();
    /**
	 * Start application
	 */
    frameLcs::_()->parseRoute();
    frameLcs::_()->init();
    frameLcs::_()->exec();

	//var_dump(frameLcs::_()->getActivationErrors()); exit();
