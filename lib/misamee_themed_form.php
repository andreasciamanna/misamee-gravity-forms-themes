<?php
if ( ! class_exists( 'Misamee_Themed_Form_Widget' ) ) {
	require_once 'misamee_themed_form_widget.php';
}

class theme_data {
	public $type;
	public $key;
	public $file;
	public $themeUrl;
	public $deps;
}

class Misamee_Themed_Form {
	public static function init() {
		$class = __CLASS__;
		new $class;
	}

	public $theme;
	public $themeName;

	public function __construct() {
		$this->themeName = false;
		$this->theme     = array();

		add_filter( "gform_shortcode_theme", array( &$this, "misamee_themed_form_theme" ), 10, 3 );
		add_filter( 'gform_pre_render', array( &$this, 'misamee_form_pre_render' ), 10, 2 );
		add_action( 'widgets_init', array( &$this, "misamee_themed_form_register_widget" ) );
	}

	function misamee_themed_form_register_widget() {
		register_widget( 'misamee_themed_form_widget' );
	}

	function misamee_form_pre_render( $form, $ajax ) {
		$formId = $form['id'];

		foreach ( $form['fields'] as $field ) {
			if ( $field['type'] == 'hidden' && $field['label'] == 'misamee-theme' ) {
				$this->themeName = $this->misamee_themed_form_setTemplate( $field['defaultValue'], $formId );
				break;
			}
		}

		if ( count( $this->theme ) != 0 ) {
			add_action( 'wp_footer', array( &$this, 'enqueue_scripts' ) );
			add_action( 'wp_footer', array( &$this, 'enqueue_styles' ) );
			add_action( 'wp_footer', array( &$this, 'enqueue_php' ) );
			add_filter( 'gform_get_form_filter', array( &$this, 'misamee_gform_get_form_filter' ), 10, 1 );
		}

		return $form;
	}

	function misamee_gform_get_form_filter( $form_string ) {
		return $this->wrap_form( $form_string );
	}

	function misamee_themed_form_theme( $string, $attributes, $content ) {
		extract( shortcode_atts( array(
			'title'        => true,
			'description'  => true,
			'id'           => 0,
			'name'         => '',
			'field_values' => "",
			'ajax'         => false,
			'tabindex'     => 1,
			'action'       => 'form',
			'themename'    => '',
			'cssclass'     => ''
		), $attributes ) );

		/** @var $themename string */
		/** @var $id int */
		if ( $themename != '' ) {
			$this->themeName = $this->misamee_themed_form_setTemplate( $themename, $id );

			if ( count( $this->theme ) != 0 ) {
				add_action( 'wp_footer', array( &$this, 'enqueue_scripts' ) );
				add_action( 'wp_footer', array( &$this, 'enqueue_styles' ) );
				add_action( 'wp_footer', array( &$this, 'enqueue_php' ) );
			}
		}

		$attributes['action'] = 'form';

		$formString = RGForms::parse_shortcode( $attributes, $content = null );

		return $this->wrap_form( $formString );
	}

	public function wrap_form( $formString ) {
		$themeName = $this->themeName;

		$additionalClasses = ( $themeName != '' ) ? ' class="themed_form ' . $themeName . '"' : "";
		if ( $additionalClasses != '' && strpos( $formString, $additionalClasses ) === false ) {
			return "<div$additionalClasses>$formString</div>";
		}

		return $formString;
	}

	private function misamee_themed_form_setTemplate( $themeName, $formId ) {
		$theme = $this->misamee_themed_form_getThemeByName( $themeName );

		$themeName = strtolower( $themeName );
		if ( $themeName != "none" ) {
			if ( $themeName == 'default' ) {
				$themeData                   = new theme_data();
				$themeData->type             = 'style';
				$themeData->key              = 'misamee-themed-form-css';
				$themeData->file             = $theme['url'] . 'css/misamee.themed.form.css';
				$this->theme[ $themeName ][] = $themeData;

				$themeData                   = new theme_data();
				$themeData->type             = 'script';
				$themeData->key              = 'tooltipsy';
				$themeData->file             = $theme['url'] . 'js/tooltipsy.source.js';
				$themeData->deps             = array( 'jquery' );
				$this->theme[ $themeName ][] = $themeData;

				$themeData                   = new theme_data();
				$themeData->type             = 'script';
				$themeData->key              = 'misamee-themed-form-js';
				$themeData->file             = $theme['url'] . 'js/misamee.themed.form.js';
				$themeData->deps             = array( 'jquery' );
				$this->theme[ $themeName ][] = $themeData;
			} elseif ( is_dir( $theme['dir'] ) ) {
				//get all files in specified directory
				$files = glob( $theme['dir'] . "/*.*" );

				foreach ( $files as $file ) {
					$fileData = pathinfo( $file );
					switch ( $fileData['extension'] ) {
						case 'css':
							$themeData                   = new theme_data();
							$themeData->type             = 'style';
							$themeData->key              = $fileData['filename'];
							$themeData->file             = $theme['url'] . $fileData['basename'];
							$themeData->themeUrl         = $theme['url'];
							$this->theme[ $themeName ][] = $themeData;
							break;
						case 'js':
							$themeData                   = new theme_data();
							$themeData->type             = 'script';
							$themeData->key              = $fileData['filename'];
							$themeData->file             = $theme['url'] . $fileData['basename'];
							$themeData->themeUrl         = $theme['url'];
							$this->theme[ $themeName ][] = $themeData;
							break;
						case 'php':
							$themeData                   = new theme_data();
							$themeData->type             = 'php';
							$themeData->key              = $fileData['filename'];
							$themeData->file             = $theme['dir'] . '/' . $fileData['basename'];
							$themeData->themeUrl         = $theme['url'];
							$this->theme[ $themeName ][] = $themeData;
							break;
					}
				}
			}
			add_action( "gform_field_css_class", array( &$this, "add_custom_class" ), 10, 3 );
		}

		return $themeName;
	}

	function enqueue_scripts() {
		$type = 'script';
		static $added = array();

		foreach ( $this->theme as $theme ) {
			if ( count( $theme ) > 0 ) {
				foreach ( $theme as $themeData ) {
					if ( $themeData->type == $type && ! array_search( $themeData->file, $added ) ) {
						wp_enqueue_script( $themeData->key, $themeData->file, $themeData->deps, false, true );
						$added[] = $themeData->file;
					}
				}
			}
		}
	}

	function enqueue_styles() {
		$type = 'style';
		static $added = array();

		foreach ( $this->theme as $theme ) {
			if ( count( $theme ) > 0 ) {
				foreach ( $theme as $themeData ) {
					if ( $themeData->type == $type && ! array_search( $themeData->file, $added ) ) {
						wp_enqueue_style( $themeData->key, $themeData->file, $themeData->deps );
						$added[] = $themeData->file;
					}
				}
			}
		}
	}

	function enqueue_php() {
		$type = 'php';
		static $added = array();

		foreach ( $this->theme as $themeName => $theme ) {
			if ( count( $theme ) > 0 ) {
				foreach ( $theme as $themeData ) {
					if ( $themeData->type == $type && ! array_search( $themeData->file, $added ) ) {
						include( $themeData->file );
						$added[] = $themeData->file;
					}
				}
			}
		}
	}

	public function add_custom_class( $classes, $field, $form ) {
		$classes .= " misamee_themed_$field[type]";

		return $classes;
	}

	private function misamee_themed_form_getThemeByName( $themeName ) {
		switch ( strtolower( $themeName ) ) {
			case 'none':
				$theme    = 'None';
				$themeDir = '';
				break;
			case 'default':
				$theme    = $themeName;
				$themeDir = Misamee_GF_Themes::getPluginPath();
				$themeUrl = Misamee_GF_Themes::getPluginUrl();
				break;
			default:
				$themes = $this->misamee_themed_form_themes();
				if ( array_key_exists( $themeName, $themes ) ) {
					$theme    = $themeName;
					$themeDir = WP_CONTENT_DIR . $themes[ $theme ]['dir'];
					$themeUrl = $themes[ $theme ]['url'];
				} elseif ( $themeName == 'Default' ) {
					$theme    = $themeName;
					$themeDir = Misamee_GF_Themes::getPluginPath();
					$themeUrl = Misamee_GF_Themes::getPluginUrl();
				}
		}

		/** @var $theme string */
		/** @var $themeDir string */
		/** @var $themeUrl string */
		return array(
			'name' => $theme,
			'dir'  => str_replace( "\\", "/", $themeDir ),
			'url'  => $themeUrl
		);
	}

	public static function misamee_themed_form_themes() {
		$themes = array();
		if ( is_dir( WP_CONTENT_DIR . '/mgft-themes' ) ) {
			//get all files in specified directory
			$files = glob( WP_CONTENT_DIR . '/mgft-themes/*', GLOB_ONLYDIR );
			ksort( $files, SORT_STRING );

			$themes['none']    = array(
				'dir' => '',
				'url' => ''
			);
			$themes['default'] = array(
				'dir' => '',
				'url' => ''
			);

			if ( is_array( $files ) && count( $files ) > 0 ) {
				foreach ( $files as $file ) {
					if ( is_dir( $file ) ) {
						$themePath            = substr( $file, strlen( WP_CONTENT_DIR ) );
						$folderArray          = explode( '/', $themePath );
						$themeName            = $folderArray[ count( $folderArray ) - 1 ];
						$themeUrl             = WP_CONTENT_URL . '/mgft-themes/' . $themeName . "/";
						$themes[ $themeName ] = array(
							'dir' => $themePath,
							'url' => $themeUrl
						);
					}
				}
			}
		}
		if ( is_dir( Misamee_GF_Themes::getPluginPath() . 'themes' ) ) {
			//get all files in specified directory
			$files = glob( Misamee_GF_Themes::getPluginPath() . 'themes/*', GLOB_ONLYDIR );
			ksort( $files, SORT_STRING );

			$themes['none']    = array(
				'dir' => '',
				'url' => ''
			);
			$themes['default'] = array(
				'dir' => '',
				'url' => ''
			);

			if ( is_array( $files ) && count( $files ) > 0 ) {
				foreach ( $files as $file ) {
					if ( is_dir( $file ) ) {
						$themePath            = substr( $file, strlen( WP_CONTENT_DIR ) );
						$folderArray          = explode( '/', $themePath );
						$themeName            = $folderArray[ count( $folderArray ) - 1 ];
						$themeUrl             = Misamee_GF_Themes::getPluginUrl() . 'themes/' . $themeName . "/";
						$themes[ $themeName ] = array(
							'dir' => $themePath,
							'url' => $themeUrl
						);
					}
				}
			}
		}

		return $themes;
	}

}
