<?php
    global $wpdb;
    if (!defined('WPLANG') || WPLANG == '') {
        define('LCS_WPLANG', 'en_GB');
    } else {
        define('LCS_WPLANG', WPLANG);
    }
    if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);

    define('LCS_PLUG_NAME', basename(dirname(__FILE__)));
    define('LCS_DIR', WP_PLUGIN_DIR. DS. LCS_PLUG_NAME. DS);
    define('LCS_TPL_DIR', LCS_DIR. 'tpl'. DS);
    define('LCS_CLASSES_DIR', LCS_DIR. 'classes'. DS);
    define('LCS_TABLES_DIR', LCS_CLASSES_DIR. 'tables'. DS);
	define('LCS_HELPERS_DIR', LCS_CLASSES_DIR. 'helpers'. DS);
    define('LCS_LANG_DIR', LCS_DIR. 'lang'. DS);
    define('LCS_IMG_DIR', LCS_DIR. 'img'. DS);
    define('LCS_TEMPLATES_DIR', LCS_DIR. 'templates'. DS);
    define('LCS_MODULES_DIR', LCS_DIR. 'modules'. DS);
    define('LCS_FILES_DIR', LCS_DIR. 'files'. DS);
    define('LCS_ADMIN_DIR', ABSPATH. 'wp-admin'. DS);

    define('LCS_SITE_URL', get_bloginfo('wpurl'). '/');
    define('LCS_JS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/js/');
    define('LCS_CSS_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/css/');
    define('LCS_IMG_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/img/');
    define('LCS_MODULES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/modules/');
    define('LCS_TEMPLATES_PATH', WP_PLUGIN_URL.'/'.basename(dirname(__FILE__)).'/templates/');
    define('LCS_JS_DIR', LCS_DIR. 'js/');

    define('LCS_URL', LCS_SITE_URL);

    define('LCS_LOADER_IMG', LCS_IMG_PATH. 'loading.gif');
	define('LCS_TIME_FORMAT', 'H:i:s');
    define('LCS_DATE_DL', '/');
    define('LCS_DATE_FORMAT', 'm/d/Y');
    define('LCS_DATE_FORMAT_HIS', 'm/d/Y ('. LCS_TIME_FORMAT. ')');
    define('LCS_DATE_FORMAT_JS', 'mm/dd/yy');
    define('LCS_DATE_FORMAT_CONVERT', '%m/%d/%Y');
    define('LCS_WPDB_PREF', $wpdb->prefix);
    define('LCS_DB_PREF', 'lcs_');
    define('LCS_MAIN_FILE', 'lcs.php');

    define('LCS_DEFAULT', 'default');
    define('LCS_CURRENT', 'current');

	define('LCS_EOL', "\n");

    define('LCS_PLUGIN_INSTALLED', true);
    define('LCS_VERSION', '1.5.3');
    define('LCS_USER', 'user');

    define('LCS_CLASS_PREFIX', 'lcsc');
    define('LCS_FREE_VERSION', false);
	define('LCS_TEST_MODE', true);
	define('LCS_DEBUG_MODE', LCS_TEST_MODE && WP_DEBUG);

    define('LCS_SUCCESS', 'Success');
    define('LCS_FAILED', 'Failed');
	define('LCS_ERRORS', 'lcsErrors');

	define('LCS_ADMIN',	'admin');
	define('LCS_LOGGED','logged');
	define('LCS_GUEST',	'guest');
	define('LCS_AGENT',	'agent');
	define('LCS_BOT',	'bot');

	define('LCS_ALL',		'all');

	define('LCS_METHODS',		'methods');
	define('LCS_USERLEVELS',	'userlevels');
	/**
	 * Framework instance code
	 */
	define('LCS_CODE', 'lcs');

	define('LCS_LANG_CODE', 'live-chat-by-supsystic');
	/**
	 * Plugin name
	 */
	define('LCS_WP_PLUGIN_NAME', 'Live Chat by Supsystic');
	/**
	 * Plugin admin area settings
	 */
	define('LCS_DEFAULT_ADMIN_TAB', 'chat-settings');
	define('LCS_ADMIN_SLUG', 'chat-wp-supsystic');
	define('LCS_ADMIN_MENU_ICON', 'dashicons-format-chat');
	/**
	 * Custom defined for plugin
	 */
	define('LCS_SHORTCODE_CLICK', 'supsystic-show-chat');
	define('LCS_JS_FUNC_CLICK', 'lcsShowChat');
	define('LCS_SHORTCODE', 'supsystic-chat');

	define('LCS_HOME_PAGE_ID', 0);
	define('LCS_DEF_ENGINE_ID', 1);

	define('LCS_AGENT_ONLINE', 'agent_online');
	define('LCS_PAGES_POSTS', 'pages_posts');
	define('LCS_COUNTRY', 'country');
	define('LCS_DAY_HOUR', 'day_hour');
	define('LCS_WEEK_DAY', 'week_day');
	define('LCS_PAGE_URL', 'page_url');
	define('LCS_TIME_ON_PAGE', 'time_on_page');
	define('LCS_ON_EXIT', 'on_exit');

	define('LCS_MORE_THEN', 'more_then');
	define('LCS_LESS_THEN', 'less_then');
	define('LCS_EQUAL', 'equal');
	define('LCS_NOT_EQUAL', 'not_equal');
	define('LCS_LIKE', 'like');
	define('LCS_NOT_LIKE', 'not_like');
	define('LCS_IS_TRUE', 'is_true');
	define('LCS_IS_FALSE', 'is_false');

	define('LCS_SHOW_CHAT', 'show_chat');
	define('LCS_SHOW_EAE_CATCH', 'show_eye_cach');
	define('LCS_AUTO_OPEN', 'auto_open');
	define('LCS_AUTO_START', 'auto_start');

	define('LCS_SES_PENDING', 'pending');
	define('LCS_SES_IN_PROGRESS', 'in_progress');
	define('LCS_SES_COMPLETE', 'complete');
	define('LCS_SES_USER_GONE', 'user_gone');
	define('LCS_SES_CANCELLED', 'cancelled');

	define('LCS_BOOL', 'bool');
	define('LCS_INT', 'int');
	define('LCS_STRING', 'str');
	define('LCS_ARRAY', 'array');
	// Default idle delay, seconds
	define('LCS_IDLE_DELAY', 7);
