<?php
class installerLcs {
	static public $update_to_version_method = '';
	static private $_firstTimeActivated = false;
	static public function init( $isUpdate = false ) {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
		$current_version = get_option($wpPrefix. LCS_DB_PREF. 'db_version', 0);
		if(!$current_version)
			self::$_firstTimeActivated = true;
		/**
		 * modules 
		 */
		if (!dbLcs::exist("@__modules")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `code` varchar(32) NOT NULL,
			  `active` tinyint(1) NOT NULL DEFAULT '0',
			  `type_id` tinyint(1) NOT NULL DEFAULT '0',
			  `label` varchar(64) DEFAULT NULL,
			  `ex_plug_dir` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE INDEX `code` (`code`)
			) DEFAULT CHARSET=utf8;"));
			dbLcs::query("INSERT INTO `@__modules` (id, code, active, type_id, label) VALUES
				(NULL, 'adminmenu',1,1,'Admin Menu'),
				(NULL, 'options',1,1,'Options'),
				(NULL, 'user',1,1,'Users'),
				(NULL, 'pages',1,1,'Pages'),
				(NULL, 'templates',1,1,'templates'),
				(NULL, 'supsystic_promo',1,1,'supsystic_promo'),
				(NULL, 'admin_nav',1,1,'admin_nav'),
				
				(NULL, 'chat',1,1,'chat'),
				(NULL, 'statistics',1,1,'statistics'),
				(NULL, 'registration',1,1,'registration'),

				(NULL, 'mail',1,1,'mail');");
		}
		/**
		 *  modules_type 
		 */
		if(!dbLcs::exist("@__modules_type")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__modules_type` (
			  `id` smallint(3) NOT NULL AUTO_INCREMENT,
			  `label` varchar(32) NOT NULL,
			  PRIMARY KEY (`id`)
			) AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;"));
			dbLcs::query("INSERT INTO `@__modules_type` VALUES
				(1,'system'),
				(6,'addons');");
		}
		/**
		 * Chat engines table
		 */
		if (!dbLcs::exist("@__chat_engines")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_engines` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`label` VARCHAR(255) NOT NULL,
				`active` TINYINT(1) NOT NULL DEFAULT '1',

				`params` TEXT NOT NULL,

				`show_on` TINYINT(1) NOT NULL DEFAULT '0',
				`show_to` TINYINT(1) NOT NULL DEFAULT '0',
				`show_pages` TINYINT(1) NOT NULL DEFAULT '0',
				
				`views` INT(11) NOT NULL DEFAULT '0',
				`unique_views` INT(11) NOT NULL DEFAULT '0',
				`actions` INT(11) NOT NULL DEFAULT '0',
				
				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
			self::addBaseEngines();
		}
		/**
		 * Chat templates table
		 */
		if (!dbLcs::exist("@__chat_templates")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_templates` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`unique_id` varchar(8) NOT NULL,
				`label` VARCHAR(255) NOT NULL,
				`original_id` INT(11) NOT NULL DEFAULT '0',
				`engine_id` INT(11) NOT NULL DEFAULT '0',
				`is_pro` TINYINT(1) NOT NULL DEFAULT '0',
				`params` TEXT NOT NULL,
				`html` TEXT NOT NULL,
				`css` TEXT NOT NULL,
				`img_preview` VARCHAR(128) NULL DEFAULT NULL,

				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`sort_order` MEDIUMINT(5) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		// Call each time we activate or update plugin - as we can have modified base templates in future
		self::addBaseChatTemplates();
		/**
		 * Chat triggers table
		 * 				
		 */
		if (!dbLcs::exist("@__chat_triggers")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_triggers` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`label` VARCHAR(255) NOT NULL,
				`active` TINYINT(1) NOT NULL DEFAULT '1',
				`engine_id` INT(11) NOT NULL DEFAULT '0',
				`actions` TEXT NOT NULL,

				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				`sort_order` MEDIUMINT(5) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Chat triggers conditions table
		 * 				
		 */
		if (!dbLcs::exist("@__chat_triggers_conditions")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_triggers_conditions` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`trigger_id` INT(11) NOT NULL DEFAULT '0',
				
				`type` TINYINT(1) NOT NULL DEFAULT '0',
				`equal` TINYINT(1) NOT NULL DEFAULT '0',
				`value` VARCHAR(1000) NOT NULL,
				
				`sort_order` MEDIUMINT(5) NOT NULL DEFAULT '0',
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
			// Call only first time user install plugin
			self::addBaseTriggers();
		}
		/**
		 * Chat triggers ShowOn pages
		 */
		if (!dbLcs::exist("@__chat_engines_show_pages")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE `@__chat_engines_show_pages` (
				`engine_id` INT(10) NOT NULL,
				`post_id` INT(10) NOT NULL,
				`not_show` TINYINT(1) NOT NULL DEFAULT '0'
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Chat sessions table
		 */
		if (!dbLcs::exist("@__chat_sessions")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_sessions` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`engine_id` INT(11) NOT NULL,
				`user_id` INT(11) NOT NULL,
				`agent_id` INT(11) NOT NULL DEFAULT '0',
				`ip` varchar(16) DEFAULT NULL,
				`status_id` TINYINT(1) NOT NULL,
				`url` varchar(255) DEFAULT NULL,

				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Chat messages
		 */
		if (!dbLcs::exist("@__chat_messages")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_messages` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`session_id` INT(11) NOT NULL,
				`user_id` INT(11) NOT NULL,
				`msg` VARCHAR(700) NOT NULL,

				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Chat users table
		 */
		if (!dbLcs::exist("@__chat_users")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE IF NOT EXISTS `@__chat_users` (
				`id` INT(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(255) DEFAULT NULL,
				`email` varchar(255) DEFAULT NULL,
				`ip` varchar(16) DEFAULT NULL,
				`wp_id` BIGINT(20) NOT NULL DEFAULT '0',
				`active` TINYINT(1) NOT NULL DEFAULT '1',
				`position` TINYINT(1) NOT NULL DEFAULT '0',
				`params` TEXT NOT NULL,

				`date_created` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8;"));
			// Init base user - admin
			self::addBaseUsers();
		}
		self::addBotUsers();
		
		/**
		* Plugin usage statistics
		*/
		if(!dbLcs::exist("@__usage_stat")) {
			dbDelta(dbLcs::prepareQuery("CREATE TABLE `@__usage_stat` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `code` varchar(64) NOT NULL,
			  `visits` int(11) NOT NULL DEFAULT '0',
			  `spent_time` int(11) NOT NULL DEFAULT '0',
			  `modify_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
			  UNIQUE INDEX `code` (`code`),
			  PRIMARY KEY (`id`)
			) DEFAULT CHARSET=utf8"));
			dbLcs::query("INSERT INTO `@__usage_stat` (code, visits) VALUES ('installed', 1)");
		}
		/**
		 * Statistics
		 */
		if (!dbLcs::exist("@__statistics")) {
			  dbDelta(dbLcs::prepareQuery("CREATE TABLE `@__statistics` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`engine_id` int(11) NOT NULL DEFAULT '0',
				`type` TINYINT(2) NOT NULL DEFAULT '0',
				`is_unique` TINYINT(1) NOT NULL DEFAULT '0',
				`date_created` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
				PRIMARY KEY (`id`)
			  ) DEFAULT CHARSET=utf8;"));
		}
		/**
		 * Countries
		 */
		if (!dbLcs::exist("@__countries")) {
			  dbDelta(dbLcs::prepareQuery("CREATE TABLE `@__countries` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`name` varchar(128) NOT NULL,
				`iso_code_2` varchar(2) DEFAULT NULL,
				`iso_code_3` varchar(3) DEFAULT NULL,
				PRIMARY KEY (`id`)
			  ) DEFAULT CHARSET=utf8;"));
			  self::_insertCountries();
		}
		installerDbUpdaterLcs::runUpdate();
		if($current_version && !self::$_firstTimeActivated) {
			self::setUsed();
		}
		update_option($wpPrefix. LCS_DB_PREF. 'db_version', LCS_VERSION);
		add_option($wpPrefix. LCS_DB_PREF. 'db_installed', 1);
	}
	static public function setUsed() {
		update_option(LCS_DB_PREF. 'plug_was_used', 1);
	}
	static public function isUsed() {
		return true;	// No welcome page for now
		//return 0;
		return (int) get_option(LCS_DB_PREF. 'plug_was_used');
	}
	static public function delete() {
		self::_checkSendStat('delete');
		global $wpdb;
		$wpPrefix = $wpdb->prefix;
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."modules`");
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."modules_type`");
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."usage_stat`");
		//$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."chat`");
		//$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."chat_show_pages`");
		$wpdb->query("DROP TABLE IF EXISTS `".$wpPrefix.LCS_DB_PREF."statistics`");
		delete_option($wpPrefix. LCS_DB_PREF. 'db_version');
		delete_option($wpPrefix. LCS_DB_PREF. 'db_installed');
	}
	static public function deactivate() {
		self::_checkSendStat('deactivate');
	}
	static private function _checkSendStat($statCode) {
		if(class_exists('frameLcs') 
			&& frameLcs::_()->getModule('supsystic_promo')
			&& frameLcs::_()->getModule('options')
		) {
			frameLcs::_()->getModule('supsystic_promo')->getModel()->saveUsageStat( $statCode );
			frameLcs::_()->getModule('supsystic_promo')->getModel()->checkAndSend( true );
		}
	}
	static public function update() {
		global $wpdb;
		$wpPrefix = $wpdb->prefix; /* add to 0.0.3 Versiom */
		$currentVersion = get_option($wpPrefix. LCS_DB_PREF. 'db_version', 0);
		if(!$currentVersion || version_compare(LCS_VERSION, $currentVersion, '>')) {
			self::init( true );
			update_option($wpPrefix. LCS_DB_PREF. 'db_version', LCS_VERSION);
		}
	}
	static private function _insertCountries() {
		dbLcs::query('INSERT INTO @__countries VALUES 
			(1, "Afghanistan", "AF", "AFG"),
			(2, "Albania", "AL", "ALB"),
			(3, "Algeria", "DZ", "DZA"),
			(4, "American Samoa", "AS", "ASM"),
			(5, "Andorra", "AD", "AND"),
			(6, "Angola", "AO", "AGO"),
			(7, "Anguilla", "AI", "AIA"),
			(8, "Antarctica", "AQ", "ATA"),
			(9, "Antigua and Barbuda", "AG", "ATG"),
			(10, "Argentina", "AR", "ARG"),
			(11, "Armenia", "AM", "ARM"),
			(12, "Aruba", "AW", "ABW"),
			(13, "Australia", "AU", "AUS"),
			(14, "Austria", "AT", "AUT"),
			(15, "Azerbaijan", "AZ", "AZE"),
			(16, "Bahamas", "BS", "BHS"),
			(17, "Bahrain", "BH", "BHR"),
			(18, "Bangladesh", "BD", "BGD"),
			(19, "Barbados", "BB", "BRB"),
			(20, "Belarus", "BY", "BLR"),
			(21, "Belgium", "BE", "BEL"),
			(22, "Belize", "BZ", "BLZ"),
			(23, "Benin", "BJ", "BEN"),
			(24, "Bermuda", "BM", "BMU"),
			(25, "Bhutan", "BT", "BTN"),
			(26, "Bolivia", "BO", "BOL"),
			(27, "Bosnia and Herzegowina", "BA", "BIH"),
			(28, "Botswana", "BW", "BWA"),
			(29, "Bouvet Island", "BV", "BVT"),
			(30, "Brazil", "BR", "BRA"),
			(31, "British Indian Ocean Territory", "IO", "IOT"),
			(32, "Brunei Darussalam", "BN", "BRN"),
			(33, "Bulgaria", "BG", "BGR"),
			(34, "Burkina Faso", "BF", "BFA"),
			(35, "Burundi", "BI", "BDI"),
			(36, "Cambodia", "KH", "KHM"),
			(37, "Cameroon", "CM", "CMR"),
			(38, "Canada", "CA", "CAN"),
			(39, "Cape Verde", "CV", "CPV"),
			(40, "Cayman Islands", "KY", "CYM"),
			(41, "Central African Republic", "CF", "CAF"),
			(42, "Chad", "TD", "TCD"),
			(43, "Chile", "CL", "CHL"),
			(44, "China", "CN", "CHN"),
			(45, "Christmas Island", "CX", "CXR"),
			(46, "Cocos (Keeling) Islands", "CC", "CCK"),
			(47, "Colombia", "CO", "COL"),
			(48, "Comoros", "KM", "COM"),
			(49, "Congo", "CG", "COG"),
			(50, "Cook Islands", "CK", "COK"),
			(51, "Costa Rica", "CR", "CRI"),
			(52, "Cote D\'Ivoire", "CI", "CIV"),
			(53, "Croatia", "HR", "HRV"),
			(54, "Cuba", "CU", "CUB"),
			(55, "Cyprus", "CY", "CYP"),
			(56, "Czech Republic", "CZ", "CZE"),
			(57, "Denmark", "DK", "DNK"),
			(58, "Djibouti", "DJ", "DJI"),
			(59, "Dominica", "DM", "DMA"),
			(60, "Dominican Republic", "DO", "DOM"),
			(61, "East Timor", "TP", "TMP"),
			(62, "Ecuador", "EC", "ECU"),
			(63, "Egypt", "EG", "EGY"),
			(64, "El Salvador", "SV", "SLV"),
			(65, "Equatorial Guinea", "GQ", "GNQ"),
			(66, "Eritrea", "ER", "ERI"),
			(67, "Estonia", "EE", "EST"),
			(68, "Ethiopia", "ET", "ETH"),
			(69, "Falkland Islands (Malvinas)", "FK", "FLK"),
			(70, "Faroe Islands", "FO", "FRO"),
			(71, "Fiji", "FJ", "FJI"),
			(72, "Finland", "FI", "FIN"),
			(73, "France", "FR", "FRA"),
			(74, "France, Metropolitan", "FX", "FXX"),
			(75, "French Guiana", "GF", "GUF"),
			(76, "French Polynesia", "PF", "PYF"),
			(77, "French Southern Territories", "TF", "ATF"),
			(78, "Gabon", "GA", "GAB"),
			(79, "Gambia", "GM", "GMB"),
			(80, "Georgia", "GE", "GEO"),
			(81, "Germany", "DE", "DEU"),
			(82, "Ghana", "GH", "GHA"),
			(83, "Gibraltar", "GI", "GIB"),
			(84, "Greece", "GR", "GRC"),
			(85, "Greenland", "GL", "GRL"),
			(86, "Grenada", "GD", "GRD"),
			(87, "Guadeloupe", "GP", "GLP"),
			(88, "Guam", "GU", "GUM"),
			(89, "Guatemala", "GT", "GTM"),
			(90, "Guinea", "GN", "GIN"),
			(91, "Guinea-bissau", "GW", "GNB"),
			(92, "Guyana", "GY", "GUY"),
			(93, "Haiti", "HT", "HTI"),
			(94, "Heard and Mc Donald Islands", "HM", "HMD"),
			(95, "Honduras", "HN", "HND"),
			(96, "Hong Kong", "HK", "HKG"),
			(97, "Hungary", "HU", "HUN"),
			(98, "Iceland", "IS", "ISL"),
			(99, "India", "IN", "IND"),
			(100, "Indonesia", "ID", "IDN"),
			(101, "Iran (Islamic Republic of)", "IR", "IRN"),
			(102, "Iraq", "IQ", "IRQ"),
			(103, "Ireland", "IE", "IRL"),
			(104, "Israel", "IL", "ISR"),
			(105, "Italy", "IT", "ITA"),
			(106, "Jamaica", "JM", "JAM"),
			(107, "Japan", "JP", "JPN"),
			(108, "Jordan", "JO", "JOR"),
			(109, "Kazakhstan", "KZ", "KAZ"),
			(110, "Kenya", "KE", "KEN"),
			(111, "Kiribati", "KI", "KIR"),
			(112, "Korea, Democratic People\'s Republic of", "KP", "PRK"),
			(113, "Korea, Republic of", "KR", "KOR"),
			(114, "Kuwait", "KW", "KWT"),
			(115, "Kyrgyzstan", "KG", "KGZ"),
			(116, "Lao People\'s Democratic Republic", "LA", "LAO"),
			(117, "Latvia", "LV", "LVA"),
			(118, "Lebanon", "LB", "LBN"),
			(119, "Lesotho", "LS", "LSO"),
			(120, "Liberia", "LR", "LBR"),
			(121, "Libyan Arab Jamahiriya", "LY", "LBY"),
			(122, "Liechtenstein", "LI", "LIE"),
			(123, "Lithuania", "LT", "LTU"),
			(124, "Luxembourg", "LU", "LUX"),
			(125, "Macau", "MO", "MAC"),
			(126, "Macedonia, The Former Yugoslav Republic of", "MK", "MKD"),
			(127, "Madagascar", "MG", "MDG"),
			(128, "Malawi", "MW", "MWI"),
			(129, "Malaysia", "MY", "MYS"),
			(130, "Maldives", "MV", "MDV"),
			(131, "Mali", "ML", "MLI"),
			(132, "Malta", "MT", "MLT"),
			(133, "Marshall Islands", "MH", "MHL"),
			(134, "Martinique", "MQ", "MTQ"),
			(135, "Mauritania", "MR", "MRT"),
			(136, "Mauritius", "MU", "MUS"),
			(137, "Mayotte", "YT", "MYT"),
			(138, "Mexico", "MX", "MEX"),
			(139, "Micronesia, Federated States of", "FM", "FSM"),
			(140, "Moldova, Republic of", "MD", "MDA"),
			(141, "Monaco", "MC", "MCO"),
			(142, "Mongolia", "MN", "MNG"),
			(143, "Montenegro", "ME", "MNE"),
			(144, "Montserrat", "MS", "MSR"),
			(145, "Morocco", "MA", "MAR"),
			(146, "Mozambique", "MZ", "MOZ"),
			(147, "Myanmar", "MM", "MMR"),
			(148, "Namibia", "NA", "NAM"),
			(149, "Nauru", "NR", "NRU"),
			(150, "Nepal", "NP", "NPL"),
			(151, "Netherlands", "NL", "NLD"),
			(152, "Netherlands Antilles", "AN", "ANT"),
			(153, "New Caledonia", "NC", "NCL"),
			(154, "New Zealand", "NZ", "NZL"),
			(155, "Nicaragua", "NI", "NIC"),
			(156, "Niger", "NE", "NER"),
			(157, "Nigeria", "NG", "NGA"),
			(158, "Niue", "NU", "NIU"),
			(159, "Norfolk Island", "NF", "NFK"),
			(160, "Northern Mariana Islands", "MP", "MNP"),
			(161, "Norway", "NO", "NOR"),
			(162, "Oman", "OM", "OMN"),
			(163, "Pakistan", "PK", "PAK"),
			(164, "Palau", "PW", "PLW"),
			(165, "Panama", "PA", "PAN"),
			(166, "Papua New Guinea", "PG", "PNG"),
			(167, "Paraguay", "PY", "PRY"),
			(168, "Peru", "PE", "PER"),
			(169, "Philippines", "PH", "PHL"),
			(170, "Pitcairn", "PN", "PCN"),
			(171, "Poland", "PL", "POL"),
			(172, "Portugal", "PT", "PRT"),
			(173, "Puerto Rico", "PR", "PRI"),
			(174, "Qatar", "QA", "QAT"),
			(175, "Reunion", "RE", "REU"),
			(176, "Romania", "RO", "ROM"),
			(177, "Russian Federation", "RU", "RUS"),
			(178, "Rwanda", "RW", "RWA"),
			(179, "Saint Kitts and Nevis", "KN", "KNA"),
			(180, "Saint Lucia", "LC", "LCA"),
			(181, "Saint Vincent and the Grenadines", "VC", "VCT"),
			(182, "Samoa", "WS", "WSM"),
			(183, "San Marino", "SM", "SMR"),
			(184, "Sao Tome and Principe", "ST", "STP"),
			(185, "Saudi Arabia", "SA", "SAU"),
			(186, "Senegal", "SN", "SEN"),
			(187, "Serbia", "RS", "SRB"),
			(188, "Seychelles", "SC", "SYC"),
			(189, "Sierra Leone", "SL", "SLE"),
			(190, "Singapore", "SG", "SGP"),
			(191, "Slovakia (Slovak Republic)", "SK", "SVK"),
			(192, "Slovenia", "SI", "SVN"),
			(193, "Solomon Islands", "SB", "SLB"),
			(194, "Somalia", "SO", "SOM"),
			(195, "South Africa", "ZA", "ZAF"),
			(196, "South Georgia and the South Sandwich Islands", "GS", "SGS"),
			(197, "Spain", "ES", "ESP"),
			(198, "Sri Lanka", "LK", "LKA"),
			(199, "St. Helena", "SH", "SHN"),
			(200, "St. Pierre and Miquelon", "PM", "SPM"),
			(201, "Sudan", "SD", "SDN"),
			(202, "Suriname", "SR", "SUR"),
			(203, "Svalbard and Jan Mayen Islands", "SJ", "SJM"),
			(204, "Swaziland", "SZ", "SWZ"),
			(205, "Sweden", "SE", "SWE"),
			(206, "Switzerland", "CH", "CHE"),
			(207, "Syrian Arab Republic", "SY", "SYR"),
			(208, "Taiwan", "TW", "TWN"),
			(209, "Tajikistan", "TJ", "TJK"),
			(210, "Tanzania, United Republic of", "TZ", "TZA"),
			(211, "Thailand", "TH", "THA"),
			(212, "Togo", "TG", "TGO"),
			(213, "Tokelau", "TK", "TKL"),
			(214, "Tonga", "TO", "TON"),
			(215, "Trinidad and Tobago", "TT", "TTO"),
			(216, "Tunisia", "TN", "TUN"),
			(217, "Turkey", "TR", "TUR"),
			(218, "Turkmenistan", "TM", "TKM"),
			(219, "Turks and Caicos Islands", "TC", "TCA"),
			(220, "Tuvalu", "TV", "TUV"),
			(221, "Uganda", "UG", "UGA"),
			(222, "Ukraine", "UA", "UKR"),
			(223, "United Arab Emirates", "AE", "ARE"),
			(224, "United Kingdom", "GB", "GBR"),
			(225, "United States", "US", "USA"),
			(226, "United States Minor Outlying Islands", "UM", "UMI"),
			(227, "Uruguay", "UY", "URY"),
			(228, "Uzbekistan", "UZ", "UZB"),
			(229, "Vanuatu", "VU", "VUT"),
			(230, "Vatican City State (Holy See)", "VA", "VAT"),
			(231, "Venezuela", "VE", "VEN"),
			(232, "Viet Nam", "VN", "VNM"),
			(233, "Virgin Islands (British)", "VG", "VGB"),
			(234, "Virgin Islands (U.S.)", "VI", "VIR"),
			(235, "Wallis and Futuna Islands", "WF", "WLF"),
			(236, "Western Sahara", "EH", "ESH"),
			(237, "Yemen", "YE", "YEM"),
			(238, "Zaire", "ZR", "ZAR"),
			(239, "Zambia", "ZM", "ZMB"),
			(240, "Zimbabwe", "ZW", "ZWE")');
	}
	static public function addBaseChatTemplates() {
		$data = array(
'nf64kdi9' => array('unique_id' => 'nf64kdi9','label' => 'Primary','original_id' => '0','engine_id' => '0','is_pro' => '0','params' => 'YTo4OntzOjU6IndpZHRoIjtzOjM6IjQwMCI7czoxMzoid2lkdGhfbWVhc3VyZSI7czoyOiJweCI7czoxMDoibWFpbl9jb2xvciI7czo3OiIjMzE5ZmRiIjtzOjE3OiJtYWluX3RpdGxlc19jb2xvciI7czo3OiIjZmZmZmZmIjtzOjg6ImJnX2NvbG9yIjtzOjc6IiNmZmZmZmYiO3M6OToiYmFyX3RpdGxlIjtzOjEyOiJMZXRcJ3MgY2hhdD8iO3M6MTQ6Im1haW5fdHh0X2NvbG9yIjtzOjc6IiMwMjAwMDAiO3M6MTA6Im9wdHNfYXR0cnMiO2E6MTp7czoxNDoiaGF2ZV9iYXJfdGl0bGUiO3M6MToiMSI7fX0=','html' => '<div class=\"lcsBar lcsChatBlock\">\r\n  	<div class=\"lcsBarTitle\">\r\n		[bar_title]\r\n    </div>\r\n  	<div class=\"lcsBarBtns\">\r\n  		<a href=\"#\" class=\"lcsBarMinimizeBtn\"></a>\r\n      	<a href=\"#\" class=\"lcsBarCloseBtn fa fa-close\"></a>\r\n  	</div>\r\n</div>\r\n<div class=\"lcsRegistration lcsChatBlock\">\r\n  	<div class=\"lcsBeforeRegTxt\">[before_reg_txt]</div>\r\n	[registration_form_start]\r\n		[user_fields]\r\n		<input type=\"submit\" name=\"register\" value=\"[register_btn_txt]\" />\r\n	[registration_form_end]\r\n</div>\r\n<div class=\"lcsWait lcsChatBlock\">\r\n	[wait_txt]\r\n</div>\r\n<div class=\"lcsChat lcsChatBlock\">\r\n  	<div class=\"lcsChatHeader\">[chat_header_txt]</div>\r\n  	<div class=\"lcsChatOperJoinedMsg\">[chat_agent_joined_txt]</div>\r\n	<div class=\"lcsMessagesShell\">\r\n      	<div class=\"lcsMessages\"></div>\r\n      	<div class=\"lcsMessagesExShell\">\r\n          <div class=\"lcsAgentMsgShell\">\r\n            <div class=\"lcsAgentNameShell\">Consultant</div>\r\n            <div class=\"lcsAgentMsg lcsMsg\">Hello Client! Please tell me - how can I help You today?</div>\r\n          </div>\r\n          <div class=\"lcsUserMsgShell\">\r\n              <div class=\"lcsUserNameShell\">User</div>\r\n              <div class=\"lcsUserMsg lcsMsg\">Hello! Really nice support, thank you!</div>\r\n          </div>\r\n          <div class=\"lcsMsgEnd\"></div>\r\n      	</div>\r\n  	</div>\r\n  	<div class=\"lcsInputShell\">\r\n  		[input_form_start]\r\n      	<div class=\"lcsMainAreaShell\">\r\n          <div class=\"lcsMainTxtShell\">\r\n              <textarea name=\"msg\" class=\"lcsChatMsg\" placeholder=\"[msg_placeholder]\"></textarea>\r\n          </div>\r\n          <div class=\"lcsMainBtnShell\">\r\n              <input type=\"submit\" name=\"send\" class=\"lcsChatSendBtn\" value=\"[send_btn_txt]\" />\r\n          </div>\r\n        </div>\r\n      	[input_form_end]\r\n  	</div>\r\n  	<div class=\"lcsChatFooter\"></div>\r\n</div>\r\n<div class=\"lcsComplete lcsChatBlock\">\r\n	[complete_txt]\r\n</div>','css' => '#[HID], #[HID] * {\r\n	-webkit-box-sizing: border-box;\r\n	-moz-box-sizing:    border-box;\r\n	box-sizing:         border-box;\r\n}\r\n#[HID] {\r\n	width: [width][width_measure];\r\n  	border: 2px solid [main_color];\r\n  	border-top-right-radius: 5px;\r\n  	border-top-left-radius: 5px;\r\n  	text-align: center;\r\n  	font-size: 14px;\r\n  	background-color: [bg_color];\r\n  	font-weight: normal;\r\n}\r\n#[HID] .lcsBar {\r\n	background-color: [main_color];\r\n  	color: [main_titles_color];\r\n  	position: relative;\r\n  	cursor: pointer;\r\n}\r\n#[HID] .lcsChatBlock {\r\n	padding: 5px;\r\n}\r\n/*bar btns*/\r\n#[HID] .lcsBarBtns {\r\n	position: absolute;\r\n  	top: 0;\r\n  	right: 5px;\r\n}\r\n#[HID] .lcsBarBtns a {\r\n	color: [main_titles_color];\r\n  	cursor: pointer;\r\n  	text-decoration: none;\r\n}\r\n#[HID] .lcsBarBtns a:hover {\r\n	text-shadow: 1px 1px 1px #333;\r\n}\r\n/*inputs*/\r\n#[HID] input[type=text],\r\n#[HID] input[type=email],\r\n#[HID] textarea {\r\n	width: 100%;\r\n  	border: 1px solid #999;\r\n  	border-radius: 5px;\r\n  	margin: 0 0 10px;\r\n  	padding: 5px;\r\n  	height: 35px;\r\n  	color: [main_txt_color];\r\n  	font-size: 14px;\r\n}\r\n#[HID] input[type=text]:hover,#[HID] input[type=text]:focus,#[HID] input[type=text]:active,\r\n#[HID] input[type=email]:hover,#[HID] input[type=email]:focus,#[HID] input[type=email]:active, \r\n#[HID] textarea:hover, #[HID] textarea:focus, #[HID] textarea:active {\r\n	border-color: [main_color];\r\n  	box-shadow: 0 0 5px [main_color];\r\n}\r\n#[HID] input:focus::-webkit-input-placeholder { color: transparent; }\r\n#[HID] input:focus:-moz-placeholder { color: transparent; }\r\n#[HID] input:focus::-moz-placeholder { color: transparent; }\r\n#[HID] input:focus:-ms-input-placeholder { color: transparent; }\r\n#[HID] textarea:focus::-webkit-input-placeholder { color: transparent; }\r\n#[HID] textarea:focus:-moz-placeholder { color: transparent; }\r\n#[HID] textarea:focus::-moz-placeholder { color: transparent; }\r\n#[HID] textarea:focus:-ms-input-placeholder { color: transparent; }\r\n\r\n#[HID] textarea {\r\n	height: auto;\r\n}\r\n#[HID] input[type=submit] {\r\n  	border: none;\r\n  	border-radius: 5px;\r\n  	margin: 0 0 10px;\r\n  	padding: 5px;\r\n  	height: 35px;\r\n  	background-color: [main_color];\r\n  	color: [main_titles_color];\r\n  	cursor: pointer;\r\n}\r\n#[HID] input[type=submit]:hover {\r\n	box-shadow: 0 0 5px [main_color];\r\n}\r\n/*registration*/\r\n#[HID] .lcsRegForm {\r\n	padding: 5px;\r\n}\r\n/*chat*/\r\n#[HID] .lcsChatAgentJoinedMsg {\r\n  color: {{ adjBs(\'[main_txt_color]\', 150) }};\r\n}\r\n#[HID] .lcsChatMsg,\r\n#[HID] .lcsChatSendBtn {\r\n	margin: 0;\r\n}\r\n#[HID] .lcsChatMsg{\r\n	width: 100%;\r\n  	height: 70px;\r\n  	border: none;\r\n  	border-radius: 0;\r\n  	resize: none;\r\n}\r\n#[HID] .lcsChatMsg:hover,#[HID] .lcsChatMsg:focus,#[HID] .lcsChatMsg:active {\r\n	box-shadow: none;\r\n  	border: none;\r\n}\r\n#[HID] .lcsMainAreaShell {\r\n  	width: 100%;\r\n  	border-top: 1px solid [main_color];\r\n  	text-align: left;\r\n}\r\n#[HID] .lcsInputShell {\r\n	margin-top: 10px;\r\n}\r\n#[HID] .lcsMainTxtShell,#[HID] .lcsMainBtnShell {\r\n	display: inline-block;\r\n  	vertical-align: middle;\r\n}\r\n#[HID] .lcsMainBtnShell {\r\n	\r\n}\r\n#[HID] .lcsMainTxtShell {\r\n	min-width: 80%;\r\n}\r\n/*messages*/\r\n#[HID] .lcsMessagesShell {\r\n	padding: 10px;\r\n}\r\n#[HID] .lcsMessages ,#[HID] .lcsMessagesExShell {\r\n	width: 100%;\r\n}\r\n#[HID] .lcsAgentMsgShell,#[HID] .lcsUserMsgShell {\r\n	clear: both;\r\n}\r\n#[HID] .lcsAgentNameShell,#[HID] .lcsUserNameShell {\r\n	color: {{ adjBs(\'[main_txt_color]\', 150) }};\r\n  	margin-bottom: 5px;\r\n  	font-size: 13px;\r\n}\r\n#[HID] .lcsAgentNameShell {\r\n	text-align: left;\r\n}\r\n#[HID] .lcsUserNameShell {\r\n	text-align: right;\r\n}\r\n#[HID] .lcsAgentMsg,#[HID] .lcsUserMsg {\r\n  	color: [main_txt_color];\r\n  	max-width: 80%;\r\n  	padding: 8px 10px;\r\n  	border: none;\r\n  	border-radius: 5px;\r\n  	position: relative;\r\n}\r\n#[HID] .lcsAgentMsg:after,#[HID] .lcsUserMsg:after {\r\n    content: \'\';\r\n    position: absolute;\r\n    border-style: solid;\r\n    display: block;\r\n    width: 0;\r\n    top: 8px;\r\n}\r\n#[HID] .lcsAgentMsg:after {\r\n	left: -8px;\r\n  	border-color: transparent {{ adjOp(\'[main_color]\', 0.3) }};\r\n  	border-width: 0 8px 8px 0;\r\n}\r\n#[HID] .lcsUserMsg:after {\r\n	right: -8px;\r\n  	border-color: transparent {{ adjOp(\'[main_txt_color]\', 0.1) }};\r\n  	border-width: 0 0 8px 8px;\r\n}\r\n#[HID] .lcsAgentMsg {\r\n	background-color: {{ adjOp(\'[main_color]\', 0.3) }};\r\n  	float: left;\r\n  	text-align: left;\r\n}\r\n#[HID] .lcsUserMsg {\r\n	background-color: {{ adjOp(\'[main_txt_color]\', 0.1) }};\r\n  	float: right;\r\n  	text-align: right;\r\n}\r\n/*clear css after all msgs*/\r\n#[HID] .lcsMsgEnd {\r\n	clear: both;\r\n}\r\n','img_preview' => 'primary.jpg','sort_order' => '0','date_created' => '2015-11-09 21:57:44'),
'f82fjg2' => array('unique_id' => 'f82fjg2','label' => 'Office','original_id' => '0','engine_id' => '0','is_pro' => '0','params' => 'YTo5OntzOjU6IndpZHRoIjtzOjM6IjMwMCI7czoxMzoid2lkdGhfbWVhc3VyZSI7czoyOiJweCI7czoxMDoibWFpbl9jb2xvciI7czo3OiIjMzE5ZmRiIjtzOjE3OiJtYWluX3RpdGxlc19jb2xvciI7czo3OiIjZmZmZmZmIjtzOjg6ImJnX2NvbG9yIjtzOjc6IiNmZmZmZmYiO3M6MTQ6Im1haW5fdHh0X2NvbG9yIjtzOjc6IiMwMjAwMDAiO3M6MTY6Im1haW5fZm9udF9mYW1pbHkiO3M6NjoiQXJhcGV5IjtzOjk6ImJhcl90aXRsZSI7czoxMjoiTGV0XCdzIGNoYXQ/IjtzOjEwOiJvcHRzX2F0dHJzIjthOjE6e3M6MTQ6ImhhdmVfYmFyX3RpdGxlIjtzOjE6IjEiO319','html' => '<div class=\"lcsBar lcsChatBlock\">\r\n  	<div class=\"lcsBarTitle\">\r\n		[bar_title]\r\n    </div>\r\n  	<div class=\"lcsBarBtns\">\r\n  		<a href=\"#\" class=\"lcsBarMinimizeBtn\"></a>\r\n      	<a href=\"#\" class=\"lcsBarCloseBtn\">-</a>\r\n  	</div>\r\n</div>\r\n<div class=\"lcsRegistration lcsChatBlock\">\r\n  	<div class=\"lcsBeforeRegTxt\">[before_reg_txt]</div>\r\n	[registration_form_start]\r\n		[user_fields]\r\n		<input type=\"submit\" name=\"register\" value=\"[register_btn_txt]\" />\r\n	[registration_form_end]\r\n</div>\r\n<div class=\"lcsWait lcsChatBlock\">\r\n	[wait_txt]\r\n</div>\r\n<div class=\"lcsContactForm lcsChatBlock\">\r\n	[contact_form_content]\r\n</div>\r\n<div class=\"lcsChat lcsChatBlock\">\r\n  	<div class=\"lcsChatHeader\">\r\n      [if enb_agent_avatar]\r\n      <div class=\"lcsAgentAvatartShell\"><img src=\"[def_avatar]\" class=\"lcsAgentAvatart\" /></div>\r\n      [endif]\r\n      <div class=\"lcsAgentName\">Agent</div>\r\n      <div class=\"lcsHeaderTxt\">[chat_header_txt]</div>\r\n      <div class=\"lcsHeaderRightSide\">\r\n        [if enb_rating]\r\n          <div class=\"lcsRateShell\">\r\n            <i class=\"lcsRateBtn lcsRatePlusBtn fa fa-thumbs-o-up\"></i>\r\n            <i class=\"lcsRateBtn lcsRateMinusBtn fa fa-thumbs-o-down\"></i>\r\n          </div>\r\n        [endif]\r\n        [if enb_opts]\r\n        <div class=\"lcsOptBtnsShell\">\r\n          <i class=\"lcsOptBtn fa fa-bars\"></i>\r\n          <div class=\"lcsOptsList\">\r\n              <div class=\"lcsOptItem lcsPrintBtn\">\r\n                  <i class=\"fa fa-print\"></i>[btn_print_txt]\r\n              </div>\r\n              <div class=\"lcsOptItem lcsSendToEmailBtn\">\r\n                  <i class=\"fa fa-envelope-o\"></i>[btn_send_to_email_txt]\r\n              </div>\r\n              <div class=\"lcsOptItem lcsSwitchSoundBtn\">\r\n                  <i class=\"fa fa-bell-slash-o\" data-switch-class=\"fa-bell-o/fa-bell-slash-o\"></i>[btn_switch_sound]\r\n              </div>\r\n          </div>\r\n        </div>\r\n        [endif]\r\n      </div>\r\n  	</div>\r\n	<div class=\"lcsMessagesShell\">\r\n      	<div class=\"lcsMessages\"></div>\r\n      	<div class=\"lcsMessagesExShell\">\r\n          <div class=\"lcsAgentMsgShell\">\r\n            <div class=\"lcsMsgHead\">\r\n              <div class=\"lcsAgentNameShell\">Consultant</div>\r\n              <div class=\"lcsMsgTime\">00:00</div>\r\n            </div>\r\n            <div class=\"lcsAgentMsg lcsMsg\">Hello Client! Please tell me - how can I help You today?</div>\r\n          </div>\r\n          <div class=\"lcsUserMsgShell\">\r\n              <div class=\"lcsMsgHead\">\r\n                <div class=\"lcsMsgTime\">00:00</div>\r\n             	<div class=\"lcsUserNameShell\">User</div>\r\n              </div>\r\n              <div class=\"lcsUserMsg lcsMsg\">Hello! Really nice support, thank you!</div>\r\n          </div>\r\n          <div class=\"lcsMsgEnd\"></div>\r\n      	</div>\r\n  	</div>\r\n  	<div class=\"lcsInputShell\">\r\n  		[input_form_start]\r\n      	<div class=\"lcsMainAreaShell\">\r\n          <div class=\"lcsMainTxtShell\">\r\n              <textarea name=\"msg\" class=\"lcsChatMsg\" placeholder=\"[msg_placeholder]\"></textarea>\r\n          </div>\r\n          <div class=\"lcsMainBtnShell\">\r\n              <button name=\"send\" class=\"lcsChatSendBtn\" title=\"[send_btn_txt]\">\r\n                  <i class=\"fa fa-paper-plane\"></i>\r\n              </button>\r\n          </div>\r\n        </div>\r\n      	[input_form_end]\r\n  	</div>\r\n  	<div class=\"lcsChatFooter\"></div>\r\n</div>\r\n<div class=\"lcsComplete lcsChatBlock\">\r\n	[complete_txt]\r\n</div>','css' => '#[HID], #[HID] *, #[HID] *::before, #[HID] *::after {\r\n	-webkit-box-sizing: border-box;\r\n	-moz-box-sizing:    border-box;\r\n	box-sizing:         border-box;\r\n}\r\n#[HID] {\r\n	width: [width][width_measure];\r\n  	border: 2px solid [main_color];\r\n  	border-top-right-radius: 5px;\r\n  	border-top-left-radius: 5px;\r\n  	text-align: center;\r\n  	font-size: 14px;\r\n  	background-color: [bg_color];\r\n  	font-weight: normal;\r\n}\r\n#[HID] .lcsBar {\r\n	background-color: [main_color];\r\n  	color: [main_titles_color];\r\n  	position: relative;\r\n  	cursor: pointer;\r\n}\r\n#[HID] .lcsChatBlock {\r\n	padding: 5px;\r\n}\r\n/*bar btns*/\r\n#[HID] .lcsBarBtns {\r\n	position: absolute;\r\n  	top: 0;\r\n  	right: 5px;\r\n}\r\n#[HID] .lcsBarBtns a {\r\n	color: [main_titles_color];\r\n  	cursor: pointer;\r\n  	text-decoration: none;\r\n  	font-size: 60px;\r\n  	font-weight: normal;\r\n  	line-height: 15px;\r\n}\r\n#[HID] .lcsBarBtns a:hover {\r\n	text-shadow: 1px 1px 1px #333;\r\n}\r\n/*header*/\r\n#[HID] .lcsChatHeader {\r\n	position: relative;\r\n}\r\n#[HID] .lcsAgentAvatartShell {\r\n	position: absolute;\r\n  	top: -60px;\r\n  	left: 10px;\r\n  	width: 80px;\r\n  	height: 80px;\r\n}\r\n#[HID] img.lcsAgentAvatart {\r\n	width: 100%;\r\n  	height: auto;\r\n  	border-radius: 50%;\r\n  	border: 2px solid [main_color];\r\n}\r\n#[HID] .lcsAgentName,\r\n#[HID] .lcsHeaderTxt {\r\n	width: 100%;\r\n  	text-align: left;\r\n  	[if enb_agent_avatar]\r\n  	padding: 0 0 0 85px;\r\n  	[else]\r\n  	padding: 0 0 0 10px;\r\n  	[endif]\r\n}\r\n#[HID] .lcsHeaderTxt {\r\n  	font-size: 12px;\r\n}\r\n#[HID] .lcsChatOperJoinedMsg {\r\n	display: none;	/*not  for this temlate*/\r\n}\r\n/*#[HID] .lcsRateShell {\r\n	position: absolute;\r\n  	top: 0;\r\n  	right: 40px;\r\n}*/\r\n#[HID] .lcsRateShell,\r\n#[HID] .lcsOptBtnsShell {\r\n	display: inline-block;\r\n}\r\n#[HID] .lcsRateShell {\r\n	margin-right: 5px;\r\n}\r\n#[HID] .lcsRateShell .lcsRateBtn,\r\n#[HID] .lcsOptBtnsShell .lcsOptBtn,\r\n#[HID] .lcsChatSendBtn {\r\n	cursor: pointer;\r\n  	font-size: 20px;\r\n}\r\n#[HID] .lcsRateShell .lcsRateBtn:hover,\r\n#[HID] .lcsOptBtnsShell .lcsOptBtn:hover,\r\n#[HID] .lcsChatSendBtn:hover {\r\n	text-shadow: 1px 1px 1px #000;\r\n}\r\n#[HID] .lcsRateShell .lcsRateBtn.lcsRatePlusBtn {\r\n	color: #04a561;	/*green*/\r\n  	margin-right: 5px;\r\n}\r\n#[HID] .lcsRateShell .lcsRateBtn.lcsRateMinusBtn {\r\n	color: #ee1c25;	/*red*/\r\n}\r\n#[HID] .lcsHeaderRightSide {\r\n	position: absolute;\r\n  	top: 0;\r\n  	right: 10px;\r\n}\r\n#[HID] .lcsOptBtnsShell .lcsOptBtn {\r\n	color: [main_color];\r\n}\r\n#[HID] .lcsOptsList {\r\n	position: absolute;\r\n  	top: 100%;\r\n  	right: 0;\r\n  	min-width: 150px;\r\n  	z-index: 99;\r\n  	background-color: [bg_color];\r\n  	border: 1px solid [main_color];\r\n}\r\n#[HID] .lcsOptsList .lcsOptItem {\r\n	padding: 10px;\r\n  	text-align: left;\r\n  	cursor: pointer;  	\r\n}\r\n#[HID] .lcsOptsList .lcsOptItem:hover {\r\n	background-color: [main_color];\r\n}\r\n#[HID] .lcsOptsList .lcsOptItem .fa {\r\n	padding: 0 10px 0 0;\r\n}\r\n/*inputs*/\r\n#[HID] input[type=text],\r\n#[HID] input[type=email],\r\n#[HID] textarea {\r\n	width: 100%;\r\n  	border: 1px solid #999;\r\n  	border-radius: 5px;\r\n  	margin: 0 0 10px;\r\n  	padding: 5px;\r\n  	height: 35px;\r\n  	color: [main_txt_color];\r\n  	font-size: 14px;\r\n}\r\n#[HID] input[type=text]:hover,#[HID] input[type=text]:focus,#[HID] input[type=text]:active,\r\n#[HID] input[type=email]:hover,#[HID] input[type=email]:focus,#[HID] input[type=email]:active, \r\n#[HID] textarea:hover, #[HID] textarea:focus, #[HID] textarea:active {\r\n	border-color: [main_color];\r\n  	box-shadow: 0 0 5px [main_color];\r\n}\r\n#[HID] input:focus::-webkit-input-placeholder { color: transparent; }\r\n#[HID] input:focus:-moz-placeholder { color: transparent; }\r\n#[HID] input:focus::-moz-placeholder { color: transparent; }\r\n#[HID] input:focus:-ms-input-placeholder { color: transparent; }\r\n#[HID] textarea:focus::-webkit-input-placeholder { color: transparent; }\r\n#[HID] textarea:focus:-moz-placeholder { color: transparent; }\r\n#[HID] textarea:focus::-moz-placeholder { color: transparent; }\r\n#[HID] textarea:focus:-ms-input-placeholder { color: transparent; }\r\n\r\n#[HID] textarea {\r\n	height: auto;\r\n}\r\n#[HID] input[type=submit] {\r\n  	border: none;\r\n  	border-radius: 5px;\r\n  	margin: 0 0 10px;\r\n  	padding: 5px;\r\n  	height: 35px;\r\n  	background-color: [main_color];\r\n  	color: [main_titles_color];\r\n  	cursor: pointer;\r\n}\r\n#[HID] input[type=submit]:hover {\r\n	box-shadow: 0 0 5px [main_color];\r\n}\r\n/*registration*/\r\n#[HID] .lcsRegForm {\r\n	padding: 5px;\r\n}\r\n/*chat*/\r\n#[HID] .lcsChatAgentJoinedMsg {\r\n  color: [main_txt_color];\r\n}\r\n#[HID] .lcsChatMsg,\r\n#[HID] .lcsChatSendBtn {\r\n	margin: 0 !important;\r\n}\r\n#[HID] .lcsChatSendBtn {\r\n	border: 2px solid [main_color];\r\n  	border-radius: 50%;\r\n  	background-color: transparent;\r\n  	padding: 4px 5px 4px 3px;\r\n  	line-height: 1;\r\n}\r\n#[HID] .lcsChatSendBtn .fa {\r\n	font-size: 20px;\r\n  	color: [main_color];\r\n}\r\n#[HID] .lcsChatMsg{\r\n	width: 100%;\r\n  	height: 45px;\r\n  	border: none;\r\n  	border-radius: 0;\r\n  	resize: none;\r\n}\r\n#[HID] .lcsChatMsg:hover,#[HID] .lcsChatMsg:focus,#[HID] .lcsChatMsg:active {\r\n	box-shadow: none;\r\n  	border: none;\r\n}\r\n#[HID] .lcsMainAreaShell {\r\n	display: table;\r\n  	width: 100%;\r\n  	border-top: 1px solid [main_color];\r\n}\r\n#[HID] .lcsInputShell {\r\n	margin-top: 10px;\r\n}\r\n#[HID] .lcsMainTxtShell,#[HID] .lcsMainBtnShell {\r\n	display: table-cell;\r\n  	vertical-align: middle;\r\n}\r\n#[HID] .lcsMainBtnShell {\r\n	width: 1%;\r\n}\r\n/*messages*/\r\n#[HID] .lcsMessagesShell {\r\n	padding: 10px 5px;\r\n}\r\n#[HID] .lcsMessages ,#[HID] .lcsMessagesExShell {\r\n	width: 100%;\r\n}\r\n#[HID] .lcsAgentMsgShell,#[HID] .lcsUserMsgShell {\r\n	clear: both;\r\n}\r\n#[HID] .lcsMsgHead {\r\n	margin-bottom: 5px;\r\n  	color: [main_txt_color];\r\n}\r\n#[HID] .lcsAgentMsgShell .lcsMsgHead {\r\n	text-align: left;\r\n  	padding: 0 0 0 5px;\r\n}\r\n#[HID] .lcsUserMsgShell .lcsMsgHead {\r\n	text-align: right;\r\n  	padding: 0 5px 0 0;\r\n}\r\n#[HID] .lcsAgentNameShell,#[HID] .lcsUserNameShell {\r\n  	display: inline-block;\r\n  	font-size: 15px;\r\n  	font-weight: normal;\r\n}\r\n#[HID] .lcsMsgTime {\r\n	font-size: 12px;\r\n  	font-weight: normal;\r\n  	display: inline-block;\r\n}\r\n#[HID] .lcsAgentNameShell {\r\n  	margin: 0 3px 0 0;\r\n}\r\n#[HID] .lcsUserNameShell {\r\n  	margin: 0 0 0 3px;\r\n}\r\n#[HID] .lcsAgentMsg,#[HID] .lcsUserMsg {\r\n  	color: [main_txt_color];\r\n  	max-width: 90%;\r\n  	padding: 8px 10px;\r\n  	border-radius: 5px;\r\n  	position: relative;\r\n}\r\n#[HID] .lcsAgentMsg {\r\n  	background-color: {{ adjOp(\'[main_txt_color]\', 0.1) }};\r\n  	float: left;\r\n  	text-align: left;\r\n  	border: 1px solid [main_txt_color];\r\n}\r\n#[HID] .lcsUserMsg {\r\n	background-color: {{ adjOp(\'[main_color]\', 0.3) }};\r\n  	float: right;\r\n  	text-align: right;\r\n  	border: 1px solid [main_color];\r\n}\r\n/*clear css after all msgs*/\r\n#[HID] .lcsMsgEnd {\r\n	clear: both;\r\n}\r\n/*common*/\r\n#[HID] p {\r\n	margin: 0;\r\n}\r\n','img_preview' => 'standard.jpg','sort_order' => '1','date_created' => '2016-02-03 18:03:01'),
);
		$uidToId = array();
		foreach($data as $uid => $d) {
			$uidToId[ $uid ] = self::installDataByUid('@__chat_templates', $uid, $d, 'original_id = 0');
		}
		// Install default template
		$defTplId = (int) dbLcs::get("SELECT id FROM @__chat_templates WHERE original_id != 0", 'one');
		if(!$defTplId) {
			$defTplData = $data['f82fjg2'];	// Let thistemplate be default for now
			$defTplData['original_id'] = $uidToId[ $defTplData['unique_id'] ];
			$defTplData['engine_id'] = LCS_DEF_ENGINE_ID;
			self::installDataByUid('@__chat_templates', $defTplData['unique_id'], $defTplData, 'original_id = 1');
		}
	}
	static public function addBaseEngines() {
		$data = array(
LCS_DEF_ENGINE_ID => array('id' => LCS_DEF_ENGINE_ID,'label' => 'Default Engine','params' => 'YToyMTp7czoxMzoiY2hhdF9wb3NpdGlvbiI7czoxMjoiYm90dG9tX3JpZ2h0IjtzOjEyOiJjaGF0X3BhZGRpbmciO2k6NDA7czoxMzoiZW5iX2RyYWdnYWJsZSI7czoxOiIxIjtzOjE2OiJlbmJfYWdlbnRfYXZhdGFyIjtpOjE7czo4OiJlbmJfb3B0cyI7aToxO3M6MTI6InNlbmRfYnRuX3R4dCI7czo0OiJTZW5kIjtzOjg6IndhaXRfdHh0IjtzOjU0OiJQbGVhc2Ugd2FpdCB3aGlsZSBvdXIgb3BlcmF0b3Igd2lsbCBzdGFydCBjb252ZXJzYXRpb24iO3M6MTU6ImNoYXRfaGVhZGVyX3R4dCI7czo3ODoiPGEgaHJlZj0iaHR0cHM6Ly9zdXBzeXN0aWMuY29tLyIgdGFyZ2V0PSJfYmxhbmsiPnN1cHN5c3RpYy5jb208L2E+IGNoYXQgZW5naW5lIjtzOjEyOiJjb21wbGV0ZV90eHQiO3M6MTIxOiJUaGFuayB5b3UgZm9yIHVzaW5nIG91ciBzdXBwb3J0ISBEcml2ZW4gYnkgPGEgaHJlZj0iaHR0cHM6Ly9zdXBzeXN0aWMuY29tLyIgdGFyZ2V0PSJfYmxhbmsiPnN1cHN5c3RpYy5jb20gY2hhdCBlbmdpbmU8L2E+IjtzOjIxOiJjaGF0X2FnZW50X2pvaW5lZF90eHQiO3M6MzY6Ik9wZXJhdG9yIFtuYW1lXSBqb2luZWQgY2hhdCB3aXRoIHlvdSI7czoxNToibXNnX3BsYWNlaG9sZGVyIjtzOjE0OiJBc2sgbWUgaGVyZS4uLiI7czoxNjoicmVnaXN0ZXJfYnRuX3R4dCI7czoxMToiU3RhcnQgQ2hhdCEiO3M6MTQ6ImJlZm9yZV9yZWdfdHh0IjtzOjI3OiJQbGVhc2UgcmVnaXN0ZXIgYmVmb3JlIGNoYXQiO3M6NDoibWFpbiI7YTozOntzOjc6InNob3dfb24iO3M6OToicGFnZV9sb2FkIjtzOjEwOiJzaG93X3BhZ2VzIjtzOjM6ImFsbCI7czo3OiJzaG93X3RvIjtzOjg6ImV2ZXJ5b25lIjt9czo5OiJlbmJfc291bmQiO2k6MTtzOjIxOiJlbmJfYWdlbnRfYXV0b191cGRhdGUiO2k6MTtzOjEwOiJyZWdfZmllbGRzIjthOjI6e2k6MDthOjU6e3M6NToibGFiZWwiO3M6NjoiRS1NYWlsIjtzOjQ6Imh0bWwiO3M6NDoidGV4dCI7czozOiJlbmIiO2k6MTtzOjk6Im1hbmRhdG9yeSI7aToxO3M6NDoibmFtZSI7czo1OiJlbWFpbCI7fWk6MTthOjQ6e3M6NToibGFiZWwiO3M6NDoiTmFtZSI7czo0OiJodG1sIjtzOjQ6InRleHQiO3M6MzoiZW5iIjtpOjE7czo0OiJuYW1lIjtzOjQ6Im5hbWUiO319czo4OiJyZWdfdHlwZSI7czozOiJhc2siO3M6OToicmVnX2V2ZW50IjtzOjExOiJiZWZvcmVfY2hhdCI7czoxMDoiZW5iX3JhdGluZyI7aTowO3M6MTA6ImlkbGVfZGVsYXkiO2k6Nzt9','show_on' => '1','show_to' => '1','show_pages' => '1'),
);
		self::installDataByUid('@__chat_engines', LCS_DEF_ENGINE_ID, $data[ LCS_DEF_ENGINE_ID ], '', 'id', true);
	}
	static public function addBaseUsers() {
		$currentUser = wp_get_current_user();
		dbLcs::query("INSERT INTO @__chat_users (name, email, ip, wp_id, active, position) 
			VALUES ('{$currentUser->display_name}', '{$currentUser->user_email}', '". utilsLcs::getIP(). "', '{$currentUser->ID}', '1', '1');");
	}
	static public function addBotUsers() {
		$botPosId = 3;	// ID for LCS_BOT position
		$botUserId = (int) dbLcs::get('SELECT id FROM @__chat_users WHERE position = '. $botPosId. '', 'one');
		if(!$botUserId) {
			dbLcs::query("INSERT INTO @__chat_users (name, email, ip, wp_id, active, position) 
				VALUES ('". __('Consultant', LCS_LANG_CODE). "', '". get_bloginfo('admin_email'). "', '". utilsLcs::getIP(). "', '0', '1', '$botPosId');");
		}
	}
	static public function installDataByUid($tbl, $uid, $data, $where = '', $idKey = 'unique_id', $ignoreUpdate = false) {
		$id = (int) dbLcs::get("SELECT id FROM `$tbl` WHERE $idKey = '$uid' ". (empty($where) ? "" : " AND $where"). "", 'one');
		if($id && $ignoreUpdate)
			return;
		$action = $id ? 'UPDATE' : 'INSERT INTO';
		$values = array();
		foreach($data as $k => $v) {
			$values[] = "$k = \"$v\"";
		}
		$valuesStr = implode(',', $values);
		$query = "$action $tbl SET $valuesStr";
		if($action == 'UPDATE')
			$query .= " WHERE $idKey = '$uid' ". (empty($where) ? "" : " AND $where"). "";
		if(dbLcs::query($query)) {
			$dataId = $action == 'UPDATE' ? $id : dbLcs::insertID();
			return $dataId;
		}
		return false;
	}
	static public function addBaseTriggers() {
		dbLcs::query('INSERT INTO @__chat_triggers (id,label,active,engine_id,actions) VALUES ("1","Eye catch","1","1","YTozOntpOjE7YTo2OntzOjM6ImVuYiI7czoxOiIxIjtzOjI6ImlkIjtzOjE6IjEiO3M6NDoiY29kZSI7czoxMzoic2hvd19leWVfY2FjaCI7czo3OiJleWVfaW1nIjtzOjMzOiJbTENTX0FTU0VUU19VUkxdaW1nL2V5ZS1jYXRjaC5wbmciO3M6ODoiYW5pbV9rZXkiO3M6MTE6ImJvdW5jZV9kb3duIjtzOjEwOiJhbmltX3NwZWVkIjtzOjQ6IjEwMDAiO31pOjI7YTozOntzOjI6ImlkIjtzOjE6IjIiO3M6NDoiY29kZSI7czoxMDoiYXV0b19zdGFydCI7czozOiJtc2ciO3M6MjY6IkhlbGxvISBIb3cgY2FuIEkgaGVscCBZb3U/Ijt9aTozO2E6Mjp7czoyOiJpZCI7czoxOiIzIjtzOjQ6ImNvZGUiO3M6OToiYXV0b19vcGVuIjt9fQ==")');
		dbLcs::query('INSERT INTO @__chat_triggers_conditions (id,trigger_id,type,equal,value,sort_order) VALUES ("1","1","7","1","YToyOntzOjM6Im1pbiI7czowOiIiO3M6Mzoic2VjIjtzOjI6IjEwIjt9","0")');
	}
}
