<?php
/*
Plugin Name: Misamee Gravity Forms Themes
Plugin URI: http://misamee.com/2012/11/misamee-gravity-forms-themes/
Description: Add the ability to specify different themes for each form. Themes can be customized.
Version: 1.3.1
Author: Misamee
Author URI: http://misamee.com/
*/
/*

== Changelog ==
See readme.txt

*/

if(!defined("RG_CURRENT_PAGE"))
    define("RG_CURRENT_PAGE", basename($_SERVER['PHP_SELF']));

if (!class_exists('misamee_tools')) {
	require_once 'lib/misamee_tools.php';
}

if (!class_exists('misamee_gf_themes')) {
	class misamee_gf_themes
	{
		/**
		 * @var string The options string name for this plugin
		 */
		//var $optionsName = 'misamee_gf_tools_options';

		var $pluginBaseName = "";

		/**
		 * @var array $options Stores the options for this plugin
		 */
		//var $options = array();
		/**
		 * @var string $localizationDomain Domain used for localization
		 */
		static $localizationDomain = "misamee-gf-themes";

		static $pluginPath;
		static $pluginUrl;

		static $themedForm;

		//Class Functions
		public static function init()
		{
			$class = __CLASS__;
			new $class;
        }

		/**
		 * PHP 5 Constructor
		 */
		public function __construct()
		{
            // full url
            if (!defined('WP_CONTENT_URL'))
                define('WP_CONTENT_URL', WP_SITEURL . '/wp-content');
            // no trailing slash, full paths only
            if (!defined('WP_CONTENT_DIR'))
                define('WP_CONTENT_DIR', ABSPATH . 'wp-content');
            // full url, no trailing slash
            if (!defined('WP_PLUGIN_URL'))
                define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');
            // full path, no trailing slash
            if (!defined('WP_PLUGIN_DIR'))
                define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');

            if (!defined('WPMU_PLUGIN_URL'))
                define('WPMU_PLUGIN_URL', WP_CONTENT_URL . '/mu-plugins');
            if (!defined('WPMU_PLUGIN_DIR'))
                define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins');

			if (self::is_gravityforms_installed()) {
				//"Constants" setup
				// Set Plugin Path
				self::$pluginPath = self::getPluginUrl();
				// Set Plugin URL
				self::$pluginUrl = self::getPluginPath();

				//Initialize the options
				//$this->getOptions();
				//Admin menu
				//add_action("admin_menu", array(&$this, "admin_menu_link"));

				//Actions
				//add_action('admin_enqueue_scripts', array(&$this,'misamee_gf_tools_script')); // or wp_enqueue_scripts, login_enqueue_scripts
				if (!class_exists('misamee_themed_form')) {
					require_once 'lib/misamee_themed_form.php';
				}
				self::$themedForm = new misamee_themed_form();

			} else {
				add_action('admin_notices', array(&$this, 'gravityFormsIsMissing'));
			}
		}

        public static function getPluginUrl() {
            return plugin_dir_url(__FILE__);
        }

        public static function getPluginPath()
        {
            return plugin_dir_path(__FILE__);
        }

		public function gravityFormsIsMissing()
		{
			misamee_tools::showMessage("You must have Gravity Forms installed in order to use this plugin.", true);
		}


		public static function is_gravityforms_installed()
		{
			return class_exists("RGForms");
		}

		function misamee_gf_tools_localization()
		{
			$locale = get_locale();
			$mo = plugins_url("/languages/" . $this->localizationDomain . "-" . $locale . ".mo", __FILE__);
			load_plugin_textdomain($this->localizationDomain, false, dirname(plugin_basename(__FILE__)) . '/languages/');
		}


		function misamee_gf_tools_filters()
		{
		}
	} //End Class
} //End if class exists statement

if (class_exists('misamee_gf_themes')) {
	add_action('plugins_loaded', array('misamee_gf_themes', 'init'));
} else {
	echo "Can't find misamee_gf_themes.";
}

