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

if ( ! defined( "RG_CURRENT_PAGE" ) ) {
	define( "RG_CURRENT_PAGE", basename( $_SERVER['PHP_SELF'] ) );
}

if ( ! class_exists( 'misamee_tools' ) ) {
	require_once 'lib/misamee_tools.php';
}

if ( ! class_exists( 'Misamee_GF_Themes' ) ) {
	class Misamee_GF_Themes {
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
		public static function init() {
			$class = __CLASS__;
			new $class;
		}

		/**
		 * PHP 5 Constructor
		 */
		public function __construct() {
			$this->check_and_set_constants();

			if ( self::is_gravityforms_installed() ) {
				self::$pluginPath = self::getPluginUrl();
				self::$pluginUrl  = self::getPluginPath();

				if ( ! class_exists( 'Misamee_Themed_Form' ) ) {
					require_once 'lib/misamee_themed_form.php';
				}
				self::$themedForm = new Misamee_Themed_Form();

			} else {
				add_action( 'admin_notices', array( &$this, 'gravityFormsIsMissing' ) );
			}
		}

		public static function getPluginUrl() {
			return plugin_dir_url( __FILE__ );
		}

		public static function getPluginPath() {
			return plugin_dir_path( __FILE__ );
		}

		public function gravityFormsIsMissing() {
			misamee_tools::showMessage( "You must have Gravity Forms installed in order to use this plugin.", true );
		}


		public static function is_gravityforms_installed() {
			return class_exists( "RGForms" );
		}

		function misamee_gf_tools_localization() {
			load_plugin_textdomain( self::$localizationDomain, false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}


		function misamee_gf_tools_filters() {
		}

		private function check_and_set_constants() {
			if ( ! defined( 'WP_CONTENT_URL' ) ) {
				define( 'WP_CONTENT_URL', wp_guess_url() . '/wp-content' );
			}

			if ( ! defined( 'WP_CONTENT_DIR' ) ) {
				define( 'WP_CONTENT_DIR', ABSPATH . 'wp-content' );
			}

			if ( ! defined( 'WP_PLUGIN_URL' ) ) {
				define( 'WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins' );
			}

			if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
				define( 'WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins' );
			}

			if ( ! defined( 'WPMU_PLUGIN_URL' ) ) {
				define( 'WPMU_PLUGIN_URL', WP_CONTENT_URL . '/mu-plugins' );
			}
			if ( ! defined( 'WPMU_PLUGIN_DIR' ) ) {
				define( 'WPMU_PLUGIN_DIR', WP_CONTENT_DIR . '/mu-plugins' );
			}
		}
	}
}

if ( class_exists( 'Misamee_GF_Themes' ) ) {
	add_action( 'plugins_loaded', array( 'misamee_gf_themes', 'init' ) );
} else {
	echo "Can't find misamee_gf_themes.";
}

