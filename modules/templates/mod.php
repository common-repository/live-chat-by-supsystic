<?php
class templatesLcs extends moduleLcs {
    protected $_styles = array();
	private $_cdnUrl = '';

	public function __construct($d) {
		parent::__construct($d);
		$this->getCdnUrl();	// Init CDN URL
	}
	public function getCdnUrl() {
		if(empty($this->_cdnUrl)) {
			if((int) frameLcs::_()->getModule('options')->get('use_local_cdn')) {
				$uploadsDir = wp_upload_dir( null, false );
				$this->_cdnUrl = $uploadsDir['baseurl']. '/'. LCS_CODE. '/';
				if(uriLcs::isHttps()) {
					$this->_cdnUrl = str_replace('http://', 'https://', $this->_cdnUrl);
				}
				dispatcherLcs::addFilter('externalCdnUrl', array($this, 'modifyExternalToLocalCdn'));
			} else {
				$this->_cdnUrl = (uriLcs::isHttps() ? 'https' : 'http'). '://supsystic-42d7.kxcdn.com/';
			}
		}
		return $this->_cdnUrl;
	}
    public function init($force = false) {
        if (is_admin() || $force) {
			if($isAdminPlugOptsPage = frameLcs::_()->isAdminPlugOptsPage() || $force) {
				$this->loadCoreJs();
				$this->loadAdminCoreJs();
				$this->loadCoreCss();
				$this->loadChosenSelects();
				frameLcs::_()->addScript('adminOptionsLcs', LCS_JS_PATH. 'admin.options.js', array(), false, true);
				add_action('admin_enqueue_scripts', array($this, 'loadMediaScripts'));
			}
			// Some common styles - that need to be on all admin pages - be careful with them
			frameLcs::_()->addStyle('supsystic-for-all-admin-'. LCS_CODE, LCS_CSS_PATH. 'supsystic-for-all-admin.css');
		}
        parent::init();
    }
	public function loadMediaScripts() {
		if(function_exists('wp_enqueue_media')) {
			wp_enqueue_media();
		}
	}
	public function loadAdminCoreJs() {
		frameLcs::_()->addScript('jquery-ui-dialog');
		frameLcs::_()->addScript('jquery-ui-slider');
		frameLcs::_()->addScript('wp-color-picker');
		frameLcs::_()->addScript('icheck', LCS_JS_PATH. 'icheck.min.js');
		$this->loadTooltipster();
	}
	public function loadCoreJs() {
		frameLcs::_()->addScript('jquery');

		frameLcs::_()->addScript('commonLcs', LCS_JS_PATH. 'common.js');
		frameLcs::_()->addScript('coreLcs', LCS_JS_PATH. 'core.js');

		$ajaxurl = admin_url('admin-ajax.php');
		$siteLang = array();
		foreach(get_translations_for_domain(LCS_LANG_CODE)->entries as $key => $data) {
			if(strpos($key, '%') === false && is_array($data->translations) && sizeof($data->translations)) {
				$siteLang[$key] = $data->translations[0];
			}
		}

		$jsData = array(
			'siteUrl'					=> LCS_SITE_URL,
			'imgPath'					=> LCS_IMG_PATH,
			'cssPath'					=> LCS_CSS_PATH,
			'loader'					=> LCS_LOADER_IMG,
			'close'						=> LCS_IMG_PATH. 'cross.gif',
			'ajaxurl'					=> $ajaxurl,
			'options'					=> frameLcs::_()->getModule('options')->getAllowedPublicOptions(),
			'LCS_CODE'					=> LCS_CODE,
			//'ball_loader'				=> LCS_IMG_PATH. 'ajax-loader-ball.gif',
			//'ok_icon'					=> LCS_IMG_PATH. 'ok-icon.png',
			'jsPath'					=> LCS_JS_PATH,
			'siteLang'					=> $siteLang
		);
		if(is_admin()) {
			$jsData['isPro'] = frameLcs::_()->getModule('supsystic_promo')->isPro();
		}
		$jsData = dispatcherLcs::applyFilters('jsInitVariables', $jsData);
		frameLcs::_()->addJSVar('coreLcs', 'LCS_DATA', $jsData);
	}
	public function loadTooltipster() {
		frameLcs::_()->addScript('tooltipster', $this->_cdnUrl. 'lib/tooltipster/jquery.tooltipster.min.js');
		frameLcs::_()->addStyle('tooltipster', $this->_cdnUrl. 'lib/tooltipster/tooltipster.css');
	}
	public function loadSlimscroll() {
		frameLcs::_()->addScript('jquery.slimscroll', $this->_cdnUrl. 'js/jquery.slimscroll.js');
	}
	public function loadCodemirror() {
		frameLcs::_()->addStyle('lcsCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.css');
		frameLcs::_()->addStyle('codemirror-addon-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.css');
		frameLcs::_()->addScript('lcsCodemirror', $this->_cdnUrl. 'lib/codemirror/codemirror.js');
		frameLcs::_()->addScript('codemirror-addon-show-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/show-hint.js');
		frameLcs::_()->addScript('codemirror-addon-xml-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/xml-hint.js');
		frameLcs::_()->addScript('codemirror-addon-html-hint', $this->_cdnUrl. 'lib/codemirror/addon/hint/html-hint.js');
		frameLcs::_()->addScript('codemirror-mode-xml', $this->_cdnUrl. 'lib/codemirror/mode/xml/xml.js');
		frameLcs::_()->addScript('codemirror-mode-javascript', $this->_cdnUrl. 'lib/codemirror/mode/javascript/javascript.js');
		frameLcs::_()->addScript('codemirror-mode-css', $this->_cdnUrl. 'lib/codemirror/mode/css/css.js');
		frameLcs::_()->addScript('codemirror-mode-htmlmixed', $this->_cdnUrl. 'lib/codemirror/mode/htmlmixed/htmlmixed.js');
	}
	public function loadCoreCss() {
		$this->_styles = array(
			'styleLcs'			=> array('path' => LCS_CSS_PATH. 'style.css', 'for' => 'admin'),
			'supsystic-uiLcs'	=> array('path' => LCS_CSS_PATH. 'supsystic-ui.css', 'for' => 'admin'),
			'dashicons'			=> array('for' => 'admin'),
			'bootstrap-alerts'	=> array('path' => LCS_CSS_PATH. 'bootstrap-alerts.css', 'for' => 'admin'),
			'icheck'			=> array('path' => LCS_CSS_PATH. 'jquery.icheck.css', 'for' => 'admin'),
			//'uniform'			=> array('path' => LCS_CSS_PATH. 'uniform.default.css', 'for' => 'admin'),
			'wp-color-picker'	=> array('for' => 'admin'),
		);
		foreach($this->_styles as $s => $sInfo) {
			if(!empty($sInfo['path'])) {
				frameLcs::_()->addStyle($s, $sInfo['path']);
			} else {
				frameLcs::_()->addStyle($s);
			}
		}
		$this->loadFontAwesome();
	}
	public function loadJqueryUi() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('jquery-ui', LCS_CSS_PATH. 'jquery-ui.min.css');
			frameLcs::_()->addStyle('jquery-ui.structure', LCS_CSS_PATH. 'jquery-ui.structure.min.css');
			frameLcs::_()->addStyle('jquery-ui.theme', LCS_CSS_PATH. 'jquery-ui.theme.min.css');
			frameLcs::_()->addStyle('jquery-slider', LCS_CSS_PATH. 'jquery-slider.css');
			$loaded = true;
		}
	}
	public function loadJqGrid() {
		static $loaded = false;
		if(!$loaded) {
			$this->loadJqueryUi();
			frameLcs::_()->addScript('jq-grid', $this->_cdnUrl. 'lib/jqgrid/jquery.jqGrid.min.js');
			frameLcs::_()->addStyle('jq-grid', $this->_cdnUrl. 'lib/jqgrid/ui.jqgrid.css');
			$langToLoad = utilsLcs::getLangCode2Letter();
			$availableLocales = array('ar','bg','bg1251','cat','cn','cs','da','de','dk','el','en','es','fa','fi','fr','gl','he','hr','hr1250','hu','id','is','it','ja','kr','lt','mne','nl','no','pl','pt','pt','ro','ru','sk','sr','sr','sv','th','tr','tw','ua','vi');
			if(!in_array($langToLoad, $availableLocales)) {
				$langToLoad = 'en';
			}
			frameLcs::_()->addScript('jq-grid-lang', $this->_cdnUrl. 'lib/jqgrid/i18n/grid.locale-'. $langToLoad. '.js');
			$loaded = true;
		}
	}
	public function loadFontAwesome() {
		frameLcs::_()->addStyle('font-awesomeLcs', 'https://maxcdn.bootstrapcdn.com/font-awesome/4.5.0/css/font-awesome.min.css');
	}
	public function loadChosenSelects() {
		frameLcs::_()->addStyle('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.min.css');
		frameLcs::_()->addScript('jquery.chosen', $this->_cdnUrl. 'lib/chosen/chosen.jquery.min.js');
	}
	public function loadDatePicker() {
		frameLcs::_()->addScript('jquery-ui-datepicker');
	}
	public function loadJqplot() {
		static $loaded = false;
		if(!$loaded) {
			$jqplotDir = $this->_cdnUrl. 'lib/jqplot/';

			frameLcs::_()->addStyle('jquery.jqplot', $jqplotDir. 'jquery.jqplot.min.css');

			frameLcs::_()->addScript('jplot', $jqplotDir. 'jquery.jqplot.min.js');
			frameLcs::_()->addScript('jqplot.canvasAxisLabelRenderer', $jqplotDir. 'jqplot.canvasAxisLabelRenderer.min.js');
			frameLcs::_()->addScript('jqplot.canvasTextRenderer', $jqplotDir. 'jqplot.canvasTextRenderer.min.js');
			frameLcs::_()->addScript('jqplot.dateAxisRenderer', $jqplotDir. 'jqplot.dateAxisRenderer.min.js');
			frameLcs::_()->addScript('jqplot.canvasAxisTickRenderer', $jqplotDir. 'jqplot.canvasAxisTickRenderer.min.js');
			frameLcs::_()->addScript('jqplot.highlighter', $jqplotDir. 'jqplot.highlighter.min.js');
			frameLcs::_()->addScript('jqplot.cursor', $jqplotDir. 'jqplot.cursor.min.js');
			frameLcs::_()->addScript('jqplot.barRenderer', $jqplotDir. 'jqplot.barRenderer.min.js');
			frameLcs::_()->addScript('jqplot.categoryAxisRenderer', $jqplotDir. 'jqplot.categoryAxisRenderer.min.js');
			frameLcs::_()->addScript('jqplot.pointLabels', $jqplotDir. 'jqplot.pointLabels.min.js');
			frameLcs::_()->addScript('jqplot.pieRenderer', $jqplotDir. 'jqplot.pieRenderer.min.js');
			$loaded = true;
		}
	}
	public function loadSortable() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addScript('jquery-ui-core');
			frameLcs::_()->addScript('jquery-ui-widget');
			frameLcs::_()->addScript('jquery-ui-mouse');

			frameLcs::_()->addScript('jquery-ui-draggable');
			frameLcs::_()->addScript('jquery-ui-sortable');
			$loaded = true;
		}
	}
	public function loadDraggable() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addScript('jquery-ui-core');
			frameLcs::_()->addScript('jquery-ui-widget');
			frameLcs::_()->addScript('jquery-ui-mouse');

			frameLcs::_()->addScript('jquery-ui-draggable');
			$loaded = true;
		}
	}
	public function loadMagicAnims() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('magic.anim', $this->_cdnUrl. 'css/magic.min.css');
			$loaded = true;
		}
	}
	public function loadCssAnims() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('animate.styles', 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/3.4.0/animate.min.css');
			$loaded = true;
		}
	}
	public function loadBootstrap() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('bootstrap', $this->_cdnUrl. 'lib/bootstrap/bootstrap.min.css');
			frameLcs::_()->addStyle('bootstrap-theme', $this->_cdnUrl. 'lib/bootstrap/bootstrap-theme.min.css');
			frameLcs::_()->addScript('bootstrap', $this->_cdnUrl. 'lib/bootstrap/bootstrap.min.js');

			frameLcs::_()->addStyle('jasny-bootstrap', $this->_cdnUrl. 'lib/bootstrap/jasny-bootstrap.min.css');
			frameLcs::_()->addScript('jasny-bootstrap', $this->_cdnUrl. 'lib/bootstrap/jasny-bootstrap.min.js');
			$loaded = true;
		}
	}
	public function loadBootstrapPartial() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('sup.bootstrap', $this->_cdnUrl. 'lib/bootstrap-partial/bootstrap.partial.min.css');
			$loaded = true;
		}
	}
	public function loadBootstrapSimple() {
		static $loaded = false;
		if(!$loaded) {
			frameLcs::_()->addStyle('bootstrap-simple', LCS_CSS_PATH. 'bootstrap-simple.css');
			$loaded = true;
		}
	}
	public function loadGoogleFont( $font ) {
		static $loaded = array();
		if(!isset($loaded[ $font ])) {
			frameLcs::_()->addStyle('google.font.'. str_replace(array(' '), '-', $font), 'https://fonts.googleapis.com/css?family='. urlencode($font));
			$loaded[ $font ] = 1;
		}
	}
}
